<?php

namespace Zubr;

use Psr\Http\Message\RequestInterface;

class ClientRequest extends Request
{
    protected function getData() : array
    {
        $operationName = $this->operationName;
        $parametersNamed = $this->parametersNamed;
        $operationRequestKey = "${operationName}Request";
        $data = [$operationRequestKey => $parametersNamed];
        return $data;
    }

    public function toClientRequest() : RequestInterface
    {
        $data = $this->getData();
        // todo: JSON
        $requestBody = json_encode($data);
        // todo: Diactoros
        $request = new \Zend\Diactoros\Request();
        $request->withMethod('POST');
        // @todo: serviceName
        $serviceName = $this->getServiceName();
        // todo: Diactoros
        $request->withUri(
            (new \Zend\Diactoros\UriFactory)->createUri("/$serviceName"));
        $request->withBody(
            (new \Zend\Diactoros\StreamFactory)->createStream($requestBody)
        );
        return $request;
    }
}
