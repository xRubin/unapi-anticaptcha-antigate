<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use unapi\anticaptcha\antigate\AntigateClient;
use unapi\anticaptcha\antigate\AntigateService;
use unapi\anticaptcha\common\dto\CaptchaSolvedDto;
use unapi\anticaptcha\common\task\ReCaptcha2Task;

class ResolveRecaptcha2Test extends TestCase
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
                    'gRecaptchaResponse' => '3AHJ_VuvYIBNBW5yyv0zRYJ75VkOKvhKj9_xGBJKnQimF72rfoq3Iy-DyGHMwLAo6a3'
                ],
                'cost' => '0.000700',
                'ip' => '127.0.0.1',
            ]))
        ]);

        $service = new AntigateService([
            'key' => 'mocked',
            'client' => new AntigateClient([
                'delay' => 0,
                'handler' => HandlerStack::create($mock)
            ])
        ]);

        $service->resolve(
            new ReCaptcha2Task([
                'siteUrl' => 'http://mywebsite.com/recaptcha/test.php',
                'siteKey' => '6Lc_aCMTAAAAABx7u2N0D1XnVbI_v6ZdbM6rYf16',
            ])
        )->then(function (CaptchaSolvedDto $solved) {
            $this->assertEquals('3AHJ_VuvYIBNBW5yyv0zRYJ75VkOKvhKj9_xGBJKnQimF72rfoq3Iy-DyGHMwLAo6a3', $solved->getCode());
        })->wait();
    }
}