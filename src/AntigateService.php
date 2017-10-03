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
use unapi\anticaptcha\common\dto\CaptchaSolvedDto;

class AntigateService implements AnticaptchaServiceInterface, LoggerAwareInterface
{
    /** @var array Api key */
    private $key;
    /** @var AntigateClient */
    private $client;
    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    private $responseClass = CaptchaSolvedDto::class;
    /** @var int */
    private $softId;
    /** @var string */
    private $languagePool = 'en';
    /** @var int */
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

        if (isset($config['responseClass']))
            $this->responseClass = $config['responseClass'];

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
            'json' => [
                'clientKey' => $this->key,
                'task' => AntigateFactory::decorate($task)->asArray(),
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

            return $this->checkReady($result->taskId, 0);
        });
    }

    /**
     * @param string $taskId
     * @param int $cnt
     * @return PromiseInterface
     */
    protected function checkReady(string $taskId, int $cnt): PromiseInterface
    {
        if ($cnt > $this->retryCount)
            return new RejectedPromise('Attempts exceeded');

        $this->logger->debug('Checking anticaptcha {taskId} ready (attempt = {attempt})', ['taskId' => $taskId, 'attempt' => $cnt]);

        return $this->client->requestAsync('POST', '/getTaskResult', [
            'json' => [
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
                return new FulfilledPromise($this->responseClass::toDto([
                    'code' => $result->solution->text
                ]));
            }
        });
    }
}