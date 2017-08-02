<?php

namespace unapi\anticaptcha\antigate;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use unapi\anticaptcha\common\AnticaptchaServiceInterface;
use unapi\anticaptcha\common\AnticaptchaTaskInterface;

use function GuzzleHttp\json_decode;

class AntigateService implements AnticaptchaServiceInterface, LoggerAwareInterface
{
    /** @var array Api key */
    private $key;

    /** @var AntigateClient */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    /** @var integer */
    private $softId;

    /** @var string */
    private $languagePool = 'en';

    /** @var integer */
    private $retryCount = 20;

    /**
     * @param array $config Service configuration settings.
     */
    public function __construct(array $config = [])
    {
        if (isset($config['key'])) {
            $this->key = $config['key'];
        } else {
            throw new \InvalidArgumentException('Antigate api key required');
        }

        if (!isset($config['client'])) {
            $this->client = new AntigateClient();
        } elseif ($config['client'] instanceof AntigateClient) {
            $this->client = $config['client'];
        } else {
            throw new \InvalidArgumentException('Client must be instance of AntigateClient');
        }

        if (!isset($config['logger'])) {
            $this->logger = new NullLogger();
        } elseif ($config['logger'] instanceof LoggerInterface) {
            $this->setLogger($config['logger']);
        } else {
            throw new \InvalidArgumentException('Logger must be instance of LoggerInterface');
        }

        if (isset($config['softId'])) {
            $this->softId = $config['softId'];
        }

        if (isset($config['languagePool'])) {
            $this->languagePool = $config['languagePool'];
        }

        if (isset($config['retryCount'])) {
            $this->retryCount = $config['retryCount'];
        }
    }

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param AnticaptchaTaskInterface $task
     * @return PromiseInterface
     */
    public function resolve(AnticaptchaTaskInterface $task): PromiseInterface
    {
        $this->logger->debug('Start antigate service');

        return $this->client->requestAsync('POST', '/createTask', [
            'form_params' => [
                'clientKey' => $this->key,
                'task' => $task->asArray(),
                'softId' => $this->softId,
                'languagePool' => $this->languagePool
            ]
        ])->then(function (ResponseInterface $response) {
            $answer = $response->getBody()->getContents();

            $this->logger->debug('Upload answer: {answer}', ['answer' => $answer]);

            $result = json_decode($answer);

            if ($result->errorId) {
                $this->logger->debug('Rejected with error {errorId}: {errorCode} - {errorDescription}', [
                    'errorId' => $result->errorId,
                    'errorCode' => $result->errorCode,
                    'errorDescription' => $result->errorDescription,
                ]);
                return new RejectedPromise($result->errorDescription);
            }

            return $this->checkReady($result->taskId);
        });
    }

    /**
     * @return PromiseInterface
     */
    public function getBalance(): PromiseInterface
    {
        $this->logger->debug('Obtain antigate balance');

        return $this->client->requestAsync('POST', '/getBalance', [
            'form_params' => [
                'clientKey' => $this->key,
            ]
        ])->then(function (ResponseInterface $response) {
            $answer = $response->getBody()->getContents();

            $this->logger->debug('Balance answer: {answer}', ['answer' => $answer]);

            $result = json_decode($answer);

            if ($result->errorId) {
                $this->logger->error('Rejected with error {errorId}: {errorCode} - {errorDescription}', [
                    'errorId' => $result->errorId,
                    'errorCode' => $result->errorCode,
                    'errorDescription' => $result->errorDescription,
                ]);
                return new RejectedPromise($result->errorDescription);
            }

            return $result->balance;
        });
    }

    /**
     * @param string $taskId
     * @param integer $cnt
     * @return PromiseInterface
     */
    protected function checkReady(string $taskId, integer $cnt = 0): PromiseInterface
    {
        if ($cnt > $this->retryCount)
            return new RejectedPromise('Attempts exceeded');

        $this->logger->debug('Checking anticaptcha {taskId} ready (attempt = {attempt})', ['taskId' => $taskId, 'attempt' => $cnt]);

        return $this->client->requestAsync('POST', '/getTaskResult', [
            'form_params' => [
                'clientKey' => $this->key,
                'taskId' => $taskId,
            ],
        ])->then(function (ResponseInterface $response) use ($taskId, $cnt) {
            $answer = $response->getBody()->getContents();

            $this->logger->debug('Task {taskId} status: {answer}', ['taskId' => $taskId, 'answer' => $answer]);

            $result = json_decode($answer);

            if ($result->errorId) {
                $this->logger->error('Rejected with error {errorId}: {errorCode} - {errorDescription}', [
                    'errorId' => $result->errorId,
                    'errorCode' => $result->errorCode,
                    'errorDescription' => $result->errorDescription,
                ]);
                return new RejectedPromise($result->errorDescription);
            }

            if ('processing' === $result->status) {
                return $this->checkReady($taskId, ++$cnt);
            }

            if ('ready' === $result->status) {
                return new FulfilledPromise($result->solution);
            }
        });
    }
}