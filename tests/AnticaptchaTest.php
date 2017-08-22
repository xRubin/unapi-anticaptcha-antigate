<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use unapi\anticaptcha\antigate\AntigateClient;
use unapi\anticaptcha\antigate\AntigateService;
use unapi\anticaptcha\antigate\task\ImageTaskDecorator;
use unapi\anticaptcha\common\task\ImageTask;


class AnticaptchaTest extends \PHPUnit_Framework_TestCase
{
    public function testResolveCaptcha()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'errorId' => 0,
                'taskId' => 12345
            ])),
            new Response(200, [], json_encode([
                'errorId' => 0,
                'status' => 'processing',
            ])),
            new Response(200, [], json_encode([
                'errorId' => 0,
                'status' => 'ready',
                'solution' => [
                    'text' => 'mf4azc'
                ],
                'cost' => '0.000700',
                'ip' => '127.0.0.1',
            ]))
        ]);

        $service = new AntigateService([
            'key' => 'mocked',
            'client' => new AntigateClient([
                'handler' => HandlerStack::create($mock)
            ])
        ]);

        $service->resolve(
            new ImageTaskDecorator(
                new ImageTask([
                    'body' => file_get_contents(__DIR__ . '/fixtures/captcha/mf4azc.png'),
                    'minLength' => 6,
                    'maxLength' => 6,
                ])
            )
        )->then(function ($code) {
            $this->assertEquals('mf4azc', $code);
        })->wait();
    }
}