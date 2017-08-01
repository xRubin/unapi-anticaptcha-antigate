<?php
namespace unapi\anticaptcha\antigate;

class Client extends \GuzzleHttp\Client
{
    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config['base_uri'] = 'http://antigate.com';

        if (!array_key_exists('delay', $config))
            $config['delay'] = 2000;

        parent::__construct($config);
    }
}