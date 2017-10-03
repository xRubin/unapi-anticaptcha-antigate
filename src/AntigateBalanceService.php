<?php

namespace unapi\anticaptcha\antigate;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use unapi\anticaptcha\antigate\dto\AntigateBalanceDto;
use unapi\interfaces\ServiceInterface;

use function GuzzleHttp\json_decode;

class AntigateBalanceService implements ServiceInterface, LoggerAwareInterface
{
    /** @var array Api key */
    private $key;
    /** @var AntigateClient */
    private $client;
    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    private $responseClass = AntigateBalanceDto::class;

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
    }

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return PromiseInterface
     */
    public function getBalance(): PromiseInterface
    {
        $this->logger->debug('Obtain antigate balance');

        return $this->client->requestAsync('POST', '/getBalance', [
            'json' => [
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

            return new FulfilledPromise($this->responseClass::toDto([
                'amount' => $result->balance
            ]));
        });
    }
}