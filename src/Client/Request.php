<?php

namespace Zubr\Client;

use Zubr;
use Psr\Http\Message\RequestInterface;

class Request extends Zubr\Request
{
    protected function getData() : array
    {
        $operationName = $this->operationName;
        $parametersNamed = $this->parametersNamed;
        $operationRequestKey = "${operationName}Request";
        $data = [$operationRequestKey => $parametersNamed];
        return $data;
    }

    /**
     * @throws \Exception
     */
    public function toPsrRequest() : RequestInterface
    {
        $data = $this->getData();
        // TODO: JSON
        $requestBody = json_encode($data);
        if ($requestBody === false) {
            throw new \Exception('Invalid data');
        }
        // TODO: Diactoros
        $psrRequest = new \Zend\Diactoros\Request();
        $psrRequest = $psrRequest->withMethod('POST');
        // TODO serviceName
        $serviceName = $this->getServiceName();
        // TODO: Diactoros
        $psrRequest = $psrRequest->withUri(
            (new \Zend\Diactoros\UriFactory)->createUri("/$serviceName"));
        $psrRequest = $psrRequest->withBody(
            (new \Zend\Diactoros\StreamFactory)->createStream($requestBody)
        );
        return $psrRequest;
    }
}
