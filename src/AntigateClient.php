<?php
namespace unapi\anticaptcha\antigate;

use GuzzleHttp\Client;

class AntigateClient extends Client
{
    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config['base_uri'] = 'http://api.anti-captcha.com';

        if (!array_key_exists('delay', $config))
            $config['delay'] = 2000;

        parent::__construct($config);
    }
}