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

class AntigateService implements AnticaptchaServiceInterface, LoggerAwareInterface
{
    /** @var array Api key */
    private $key;

    /** @var Client */
    private $client;

    /** @var LoggerInterface */
    private $logger;

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
            $this->client = new Client();
        } elseif ($config['client'] instanceof Client) {
            $this->client = $config['client'];
        } else {
            throw new \InvalidArgumentException('Invalid client');
        }

        if (!isset($config['logger'])) {
            $this->logger = new NullLogger();
        } elseif ($config['logger'] instanceof LoggerInterface) {
            $this->setLogger($config['logger']);
        } else {
            throw new \InvalidArgumentException('Logger must be instance of LoggerInterface');
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
     * @param string $image
     * @param array $params
     * @return PromiseInterface
     */
    public function decodeImage(string $image, array $params = []): PromiseInterface
    {
        $this->logger->debug('Start antigate service');

        return $this->client->requestAsync('POST', '/in.php', [
            'form_params' => array_merge(
                [
                    'method' => 'base64',
                    'key' => $this->key,
                    'body' => base64_encode($image),
                    'ext' => 'jpg',
                ],
                $params
            )
        ])->then(function (ResponseInterface $response) {
            $answer = $response->getBody()->getContents();

            $this->logger->debug('Upload answer: {answer}', ['answer' => $answer]);

            if (substr($answer, 0, 2) !== 'OK') {
                $this->logger->debug('Rejected with answer: {answer}', ['answer' => $answer]);
                return new RejectedPromise($answer);
            }

            return $this->checkReady(substr($answer, 3));
        });
    }

    /**
     * @param string $id
     * @return PromiseInterface
     */
    protected function checkReady($id): PromiseInterface
    {
        $this->logger->debug('Checking anticaptcha {id} ready', ['id' => $id]);
        return $this->client->requestAsync('POST', '/res.php', [
            'form_params' => [
                'key' => $this->key,
                'action' => 'get',
                'id' => $id,
            ],
        ])->then(function (ResponseInterface $response) use ($id) {
            $answer = $response->getBody()->getContents();
            $this->logger->debug('Task {id} status: {answer}', ['id' => $id, 'answer' => $answer]);

            if (substr($answer, 0, 5) === 'ERROR') {
                $this->logger->debug('Rejected with answer: {answer}', ['answer' => $answer]);
                return new RejectedPromise($answer);
            }

            if (substr($answer, 0, 2) !== 'OK') {
                $this->logger->debug('Retrying check task {id}', ['id' => $id]);
                return $this->checkReady($id);
            }

            return new FulfilledPromise(substr($answer, 3));
        });
    }
}