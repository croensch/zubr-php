<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once(__DIR__ . '/vendor/autoload.php');

function myOperation ($in) {
    return $in * 42;
}

$requestBody = json_encode(['myOperationRequest' => ['in' => 1337]], JSON_PRETTY_PRINT);

$request = json_decode($requestBody, true);

$operationRequestWrapper = key($request);

$operation = preg_filter('/^(.*?)Request$/', '$1', $operationRequestWrapper);

$parametersNamed = $request[$operationRequestWrapper];
$parametersPositional = array_values($parametersNamed);

$result = $operation(...$parametersPositional);

$_format = isset($argv[1]) ? $argv[1] : 'json';

$responseBody = "";
if ($_format == 'xml') {
    $responseBody .= <<<XML
<${operation}Response>
    <${operation}Result>$result</${operation}Result>
</${operation}Response>

XML;
// Exception/Error in most languages have a 'message', SoapFault had 'faultstring' or 'reasonText', so just go for 'message'
// Exception/Error in most languages have no concept of "Server/Client" or "Sender/Receiver, we could call it "Caller/Callee" - or leave it out
// Exception/Error in most languages do not have a "code", make it optional/extendable
$responseBody .= <<<XML
<${operation}Response http:statusCode="404" http:reasonPhrase="Not Found">
    <prot:Fault message="Computation Failed" php:code="999">
        <prot:Detail>
            <myDetail in="${parametersPositional[0]}"/>
        </prot:Detail>
    </prot:Fault>
</${operation}Response>

XML;

} else {
    $responseBody .= json_encode([
        "${operation}Response" => [
            "${operation}Result" => $result
        ]
    ], JSON_PRETTY_PRINT) . "\n";
    $responseBody .= json_encode([
        "${operation}Response" => [
            'statusCode' => 404,
            'reasonPhrase' => 'Not Found',
            'Fault' => [
                'Detail' => [
                    'myDetail' => [
                        'in' => $parametersPositional[0]
                    ]
                ]
            ]
        ]
    ], JSON_PRETTY_PRINT);
}

echo "$requestBody\n";
echo "\n";
echo "$responseBody\n";

return;

/**
 * SERVER
 */

class ServiceServer
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function call($operationName, $parameters)
    {
        $serviceName = $this->name;
        $serviceClassName = ucfirst($serviceName);        
        $service = new $serviceClassName();
        $parametersPositional = array_values($parameters);
        $result = $service->$operationName(...$parametersPositional);
        return $result;
    }
}

/* D
function middlewareD ($serverRequest) {
    $request = ServerRequest::fromServerRequest($serverRequest);
    $serviceName = $request->getServiceName();
    $serviceServer = new ServiceServer($serviceName); 
    $operationName = $request->getOperationName();
    $parametersNamed = $request->getParametersNamed();
    $fault = null;
    $result = null;
    try {
        $result = $serviceServer->call($operationName, $parametersNamed);
    } catch (Exception $e) {
        $fault = new PhpFault($e->getMessage(), $e->getCode());
        if (isset($exception->detail) && is_object($exception->detail)) {
            $fault->setDetail($exception->detail);
        }
    }
    if ($fault) {
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_I_S_E, HttpResponse::REASON_PHRASE_I_S_E);
        $httpResponse->setFault($fault);
    } else {
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_OK);
        $httpResponse->setResult($result);
    }
    $serverResponse = $httpResponse->toServerResponse();
    return $serverResponse;
} */

/* C */
function middlewareC ($serverRequest) {
    $request = ServerRequest::fromServerRequest($serverRequest);
    $serviceName = $request->getServiceName();
    $serviceServer = new ServiceServer($serviceName); 
    $operationName = $request->getOperationName();
    $parametersNamed = $request->getParametersNamed();
    try {
        $result = $serviceServer->call($operationName, $parametersNamed);
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_OK);
        $httpResponse->setResult($result);
    } catch (Exception $e) {
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_I_S_E, HttpResponse::REASON_PHRASE_I_S_E);
        $httpResponse->setFault(new PhpFault($e->getMessage(), $e->getCode()));
        $httpResponse->getFault()->setDetail(new stdClass);
    }
    $serverResponse = $httpResponse->toServerResponse();
    return $serverResponse;
}

/* B */
function middlewareB ($serverRequest) {
    $serviceName = $serverRequest->getAttribute(ServerRequest::ATTR_SERVICE_NAME);
    $serviceServer = new ServiceServer($serviceName);
    $operationName = $serverRequest->getAttribute(ServerRequest::ATTR_OPERATION_NAME);
    $parametersNamed = $serverRequest->getAttribute(ServerRequest::ATTR_PARAMETERS_NAMED);
    try {
        $result = $serviceServer->call($operationName, $parametersNamed);
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_OK);
        $httpResponse->setResult($result);
    } catch (Exception $e) {
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_I_S_E, HttpResponse::REASON_PHRASE_I_S_E);
        $httpResponse->setFault(new PhpFault($e->getMessage(), $e->getCode()));
        $httpResponse->getFault()->setDetail(new stdClass);
    }
    $serverResponse = $httpResponse->toServerResponse();
    return $serverResponse;
}

/* A */
function middlewareA ($serverRequest) {
    $serviceName = $serverRequest->getAttribute(ServerRequest::ATTR_SERVICE_NAME);
    $serviceClassName = ucfirst($serviceName);
    $service = new $serviceClassName();
    $parametersNamed = $serverRequest->getAttribute(ServerRequest::ATTR_PARAMETERS_NAMED);
    $parametersPositional = array_values($parametersNamed);
    $operationName = $serverRequest->getAttribute(ServerRequest::ATTR_OPERATION_NAME);
    try {
        $result = $service->$operationName(...$parametersPositional);
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_OK);
        $httpResponse->setResult($result);
    } catch (Exception $e) {
        $httpResponse = new HttpServerResponse(HttpResponse::STATUS_CODE_I_S_E, HttpResponse::REASON_PHRASE_I_S_E);
        $httpResponse->setFault(new PhpFault($e->getMessage(), $e->getCode()));
        $httpResponse->getFault()->setDetail(new stdClass);
    }
    $serverResponse = $httpResponse->toServerResponse();
    return $serverResponse;
}

/**
 * CLIENT
 */

abstract class AbstractServiceClient
{
    protected $name;

    /**
     * @param string
     * @param array
     * @return mixed
     */
    public function __call($name, $args)
    {
        $operationName = $name;
        $parametersPositional = $args;
        $result = $this->call($operationName, $parametersPositional);
        return $result;
    }

    /**
     * @param string
     * @param array
     * @return mixed
     */
    protected function call($operationName, $parameters)
    {
        $httpClient = new HttpClient();

        $serviceName = $this->name;
        $request = new ClientRequest();
        $request->setServiceName($serviceName);
        $request->setOperationName($operationName);
        $request->setParameters($parameters);
        $clientRequest = $request->toClientRequest();
        $clientResponse = $httpClient->send($clientRequest);
        $response = ClientResponse::fromClientResponse($clientResponse);
        if ($fault = $response->getFault()) {
            if ($fault instanceof PhpFault) {
                throw new Exception($fault->getMessage(), $fault->getCode());
            } else {
                throw new Exception($fault->getMessage());
            }
        } else {
            return $response->getResult();
        }
    }
}

# ad-hoc

class ConcreteServiceClient extends AbstractServiceClient
{
    public function __construct($name)
    {
        $this->name = $name;
    }
}

$concreteServiceClient = new ConcreteServiceClient('myService');
$concreteServiceClient->myOperation(1337);


# static

class MyServiceClient extends AbstractServiceClient
{
    protected $name = 'myService';

    /**
     * @param int
     * @return int
     */
    public function myOperation($in)
    {
        $operationName = __METHOD__;
        $parametersNamed = ['in' => $in];
        $result = $this->call($operationName, $parametersNamed);
        return $result;
    }
}

$myServiceClient = new MyServiceClient();
$myServiceClient->myOperation(1337);
