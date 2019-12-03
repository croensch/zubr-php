<?php

namespace Zubr\Server;

use Zubr;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class Request extends Zubr\Request
{
    /**
     * @deprecated
     */
    const ATTR_SERVICE_NAME = 'serviceName';
    /**
     * @deprecated
     */
    const ATTR_OPERATION_NAME = 'operationName';
    /**
     * @deprecated
     */
    const ATTR_PARAMETERS_NAMED = 'parametersNamed';

    public static function fromPsrRequest(RequestInterface $psrRequest) : self
    {
        $requestBody = (string) $psrRequest->getBody();
        // TODO JSON
        $data = json_decode($requestBody, true);
        // TODO serviceName
        $serviceName = preg_filter('/^\/(.*?)$/', '$1', $psrRequest->getUri()->getPath());
        $operationRequestKey = key($data);
        $operationName = preg_filter('/^(.*?)Request$/', '$1', $operationRequestKey);
        $parametersNamed = $data[$operationRequestKey];
        $serverRequest = new static($serviceName, $operationName, $parametersNamed);
        return $serverRequest;
    }

    public static function fromPsrServerRequest(ServerRequestInterface $psrServerRequest) : self
    {
        $serverRequest = static::fromPsrRequest($psrServerRequest);
        return $serverRequest;
    }
}
