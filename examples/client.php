<?php

require_once(__DIR__ . '/../vendor/autoload.php');

$responseBodyJSON = json_encode([
    'myOperationResponse' => [
        'myOperationResult' => 1337 * 42,
        'statusCode' => 200,
        'reasonPhrase' => 'OK'
    ]
]);

$httpClient = new Http\Mock\Client();

$context = new Zubr\ServiceClientContext();
$context->setHttpClient($httpClient);

$psrServerResponse = new \Zend\Diactoros\Response();
$psrServerResponse = $psrServerResponse->withBody(
    (new \Zend\Diactoros\StreamFactory)->createStream($responseBodyJSON)
);
$httpClient->addResponse($psrServerResponse);

$concreteServiceClient = Zubr\ServiceClient::create('myService', $context);
$result = $concreteServiceClient->myOperation(1337);

var_dump($result);
