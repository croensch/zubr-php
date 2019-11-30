<?php

namespace Zubr;

use Http\Client\HttpClient;
use Http\Adapter\Guzzle6;

class ServiceClientContext
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    public static function createDefault() : self
    {
        $context = new static();
        $context->httpClient = new Guzzle6\Client();
        return $context;
    }

    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getHttpClient() : HttpClient
    {
        return $this->httpClient;
    }
}
