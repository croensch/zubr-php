<?php

require_once(__DIR__ . '/../vendor/autoload.php');

class MyService
{
    public function myOperation (int $in) : int {
        throw new Exception('in', $in);
        return $in * 42;
    }
}

$requestBodyJSON = json_encode([
    'myOperationRequest' => [
        'in' => 1337
    ]
]);

$psrServerRequest = new Zend\Diactoros\ServerRequest();
$psrServerRequest = $psrServerRequest->withUri(
    (new \Zend\Diactoros\UriFactory)->createUri('/myService')
);    
$psrServerRequest = $psrServerRequest->withBody(
    (new Zend\Diactoros\StreamFactory)->createStream($requestBodyJSON)
);

function middlewareD (Psr\Http\Message\ServerRequestInterface $serverRequest) : Psr\Http\Message\ResponseInterface {
    $request = Zubr\ServerRequest::fromServerRequest($serverRequest);
    $serviceName = $request->getServiceName();
    $serviceServer = new Zubr\ServiceServer($serviceName); 
    $operationName = $request->getOperationName();
    $parametersNamed = $request->getParametersNamed();
    $fault = null;
    $result = null;
    try {
        $result = $serviceServer->call($operationName, $parametersNamed);
    } catch (Exception $exception) {
        $fault = new Zubr\PhpFault($exception->getMessage(), (int) $exception->getCode());
        if (isset($exception->detail) && is_object($exception->detail) && $exception->detail instanceof stdClass) {
            $fault->setDetail($exception->detail);
        }
    }
    if ($fault) {
        $httpResponse = new Zubr\HttpServerResponse($request, Zubr\HttpServerResponse::STATUS_CODE_I_S_E, Zubr\HttpServerResponse::REASON_PHRASE_I_S_E);
        $httpResponse->setFault($fault);
    } else {
        $httpResponse = new Zubr\HttpServerResponse($request, Zubr\HttpServerResponse::STATUS_CODE_OK, Zubr\HttpServerResponse::REASON_PHRASE_OK);
        $httpResponse->setResult($result);
    }
    $serverResponse = $httpResponse->toServerResponse();
    return $serverResponse;
}

$psrServerResponse = middlewareD($psrServerRequest);

echo $psrServerResponse->getBody();

echo "\n\n";
