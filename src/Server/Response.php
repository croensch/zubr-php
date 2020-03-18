<?php

namespace Zubr\Server;

use Zubr;
use Psr\Http\Message\ResponseInterface;

class Response extends Zubr\Response
{
    protected function getData() : array
    {
        $data = [];
        $fault = $this->getFault();
        if ($fault === null) {
            $operationName = $this->getOperationName();
            $operationResponseKey = "${operationName}Response";
            $operationResultKey = "${operationName}Result";
            $data[$operationResponseKey][$operationResultKey] = $this->getResult();
        } else {
            $data['Fault'] = ['message' => $fault->getMessage()];
            if ($faultDetail = $fault->getDetail()) {
                $data['Fault']['Detail'] = $faultDetail;
            }
            if ($fault instanceof Zubr\PhpFault) {
                $data['Fault']['code'] = $fault->getCode();
            }
        }
        return $data;
    }

    /**
     * @throws \Exception
     */
    public function toPsrResponse(ResponseInterface $psrResponse = null) : ResponseInterface
    {
        $data = $this->getData();
        // TODO JSON
        $responseBody = json_encode($data);
        if ($responseBody === false) {
            throw new \Exception('Invalid data');
        }
        // TODO Diactoros
        if ($psrResponse === null) {
            $psrResponse = new \Zend\Diactoros\Response();
        }
        $psrResponse = $psrResponse->withBody(
            (new \Zend\Diactoros\StreamFactory)->createStream($responseBody)
        );
        return $psrResponse;
    }
}
