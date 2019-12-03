<?php

namespace Zubr\Client;

use Zubr;
use Psr\Http\Message\ResponseInterface;

class Response extends Zubr\Response
{
    public static function fromClientResponse(Zubr\Request $request, ResponseInterface $clientResponse) : self
    {
        $response = new static($request);
        $responseBody = (string) $clientResponse->getBody();
        // TODO JSON
        $data = json_decode($responseBody);
        $fault = null;
        $result = null;
        if (isset($data->Fault)) {
            $dataFault = $data->Fault;
            if (isset($dataFault->code)) {
                $fault = new Zubr\PhpFault($dataFault->message, $dataFault->code);
            } else {
                $fault = new Zubr\Fault($dataFault->message);
            }
        } else {
            $operationName = $request->getOperationName();
            $operationResponseProperty = "${operationName}Response";
            $operationResultProperty = "${operationName}Result";
            $result = $data->$operationResponseProperty->$operationResultProperty;
        }
        if ($fault) {
            $response->setFault($fault);
        } else {
            $response->setResult($result);
        }
        return $response;
    }
}
