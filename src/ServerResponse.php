<?php

namespace Zubr;

use Psr\Http\Message\ResponseInterface;

class ServerResponse extends Response
{
    protected function getData() : array
    {
        $operationName = $this->getRequest()->getOperationName();
        $operationResponseKey = "${operationName}Response";
        $data = [$operationResponseKey => []];
        if ($fault = $this->getFault()) {
            $data[$operationResponseKey]['Fault'] = ['message' => $fault->getMessage()];
            if ($faultDetail = $fault->getDetail()) {
                $data[$operationResponseKey]['Fault']['Detail'] = $faultDetail;
            }
            if ($fault instanceof PhpFault) {
                $data[$operationResponseKey]['Fault']['code'] = $fault->getCode();
            }
        } else {
            $operationResultKey = "${operationName}Result";
            $data[$operationResponseKey][$operationResultKey] = $this->result; 
        }
        return $data;
    }

    /**
     * @throws \Exception
     */
    public function toServerResponse() : ResponseInterface
    {
        $data = $this->getData();
        // TODO JSON
        $responseBody = json_encode($data);
        if ($responseBody === false) {
            throw new \Exception('Invalid JSON');
        }
        // TODO Diactoros
        $response = new \Zend\Diactoros\Response();
        $response = $response->withBody(
            (new \Zend\Diactoros\StreamFactory)->createStream($responseBody)
        );
        return $response;
    }
}
