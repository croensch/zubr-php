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

    /**
     * @throws \Exception
     */
    public function toClientRequest() : RequestInterface
    {
        $data = $this->getData();
        // TODO: JSON
        $requestBody = json_encode($data);
        if ($requestBody === false) {
            throw new \Exception('Invalid data');
        }
        // TODO: Diactoros
        $request = new \Zend\Diactoros\Request();
        $request->withMethod('POST');
        // TODO serviceName
        $serviceName = $this->getServiceName();
        // TODO: Diactoros
        $request->withUri(
            (new \Zend\Diactoros\UriFactory)->createUri("/$serviceName"));
        $request->withBody(
            (new \Zend\Diactoros\StreamFactory)->createStream($requestBody)
        );
        return $request;
    }
}
