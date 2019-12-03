<?php

namespace Zubr\Server;

use Zubr;
use Psr\Http\Message\ResponseInterface;

class Response extends Zubr\Response
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
    public function toPsrResponse() : ResponseInterface
    {
        $data = $this->getData();
        // TODO JSON
        $responseBody = json_encode($data);
        if ($responseBody === false) {
            throw new \Exception('Invalid JSON');
        }
        // TODO Diactoros
        $psrResponse = new \Zend\Diactoros\Response();
        $psrResponse = $psrResponse->withBody(
            (new \Zend\Diactoros\StreamFactory)->createStream($responseBody)
        );
        return $psrResponse;
    }
}
