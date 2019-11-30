<?php

namespace Zubr;

use Psr\Http\Message\ResponseInterface;

class ClientResponse extends Response
{
    public static function fromClientResponse(Request $request, ResponseInterface $clientResponse) : self
    {
        $response = new static($request);
        $responseBody = (string) $clientResponse->getBody();
        // @todo: JSON
        $data = json_decode($responseBody);
        $fault = null;
        $result = null;
        if (isset($data->Fault)) {
            $dataFault = $data->Fault;
            if (isset($dataFault->code)) {
                $fault = new PhpFault($dataFault->message, $dataFault->code);
            } else {
                $fault = new Fault($dataFault->message);
            }
        } else {
            $result = $data->result;
        }
        if ($fault) {
            $response->setFault($fault);
        } else {
            $response->setResult($result);
        }
        return $response;
    }
}
