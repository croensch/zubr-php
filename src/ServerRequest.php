<?php

namespace Zubr;

use Psr\Http\Message\RequestInterface;

class ServerRequest extends Request
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

    /**
     * @return ServerRequest
     */
    public static function fromServerRequest(RequestInterface $serverRequest) : self
    {
        $requestBody = (string) $serverRequest->getBody();
        // TODO JSON
        $data = json_decode($requestBody, true);
        // TODO serviceName
        $serviceName = preg_filter('/^\/(.*?)$/', '$1', $serverRequest->getUri()->getPath());
        $operationRequestKey = key($data);
        $operationName = preg_filter('/^(.*?)Request$/', '$1', $operationRequestKey);
        $parametersNamed = $data[$operationRequestKey];
        $request = new static($serviceName, $operationName, $parametersNamed);
        return $request;
    }
}
