<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once(__DIR__ . '/vendor/autoload.php');

class Request
{
    /**
     * @var string
     */
    protected $serviceName;

    /**
     * @var string
     */
    protected $operationName;

    /**
     * @var array
     */
    protected $parametersNamed;

    /**
     * @param string $serviceName
     * @param string $operationName
     * @param array $parametersNamed
     */
    public function __construct(string $serviceName, string $operationName, array $parametersNamed)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @deprecated
     */
    public function getBody()
    {
        return json_encode(['myOperationRequest' => ['in' => 1337]], JSON_PRETTY_PRINT);
    }
}

class ClientRequest extends Request
{
    /**
     * @return Psr\Http\Message\RequestInterface
     */
    public function toClientRequest() : Psr\Http\Message\RequestInterface
    {
        $serviceName = $this->serviceName();
        $operationName = $this->operationName;
        $parametersNamed = $this->parametersNamed;
        $operationRequestKey = "${operationName}Request";
        $data = [$operationRequestKey => $parametersNamed];
        // todo: JSON
        $requestBody = json_encode($data);
        // todo: Diactoros
        $request = new Zend\Diactoros\Request();
        $request->withMethod('POST');
        // @todo: serviceName
        $request->withUri(UriFactory::createUri("/$serviceName"));
        $request->withBody($requestBody);
        return $request;
    }
}

class ServerRequest extends Request
{
    /**
     * @deprecated
     */
    const ATTR_SERVICE_NAME = 'serviceName';
    /**
     * @deprecated
     */
    const ATTR_OPERATION_NAME = 'operationName';
    /**
     * @deprecated
     */
    const ATTR_PARAMETERS_NAMED = 'parametersNamed';

    /**
     * @return ServerRequest
     */
    public static function fromServerRequest($serverRequest)
    {
        $requestBody = $serverRequest->getBody();
        // @todo: JSON
        $data = json_decode($requestBody);
        // @todo: serviceName
        $serviceName = preg_filter('/^/(.*?)$/', '$1', $serverRequest->getUri()->getPath());
        $operationRequestKey = key($data);
        $operationName = preg_filter('/^(.*?)Request$/', '$1', $operationRequestKey);
        $parametersNamed = $data[$operationRequestKey];
        $request = new static($serviceName, $operationName, $parametersNamed);
        return $request;
    }
}

class Response
{
    /**
     * @var Fault
     */
    protected $fault;

    /**
     * @pvar mixed
     */
    protected $result;

    /**
     * @return Fault
     */
    public function getFault() : Fault
    {
        return $this->fault;
    }

    /**
     * @param Fault $fault
     */
    public function setFault(Fault $fault)
    {
        $this->result = null;
        $this->fault = $fault;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->fault = null;
        $this->result = $result;
    }
}

class ClientResponse extends Response
{
    /**
     * @return ClientResponse
     */
    public static function fromClientResponse(Psr\Http\Message\ResponseInterface $clientResponse)
    {
        $response = new static();
        $responseBody = $clientResponse->getBody();
        // @todo: JSON
        $data = json_decode($responseBody);
        $fault = null;
        $result = null;
        if (isset($data->fault)) {
            $dataFault = $data->fault;
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

class ServerResponse extends Response
{
    /**
     * @return array
     */
    protected function getData()
    {
        $operationResponseKey = "${operation}Response";
        $data = [$operationResponseKey => []];
        if ($fault = $this->fault) {
            $data[$operationResponseKey]['Fault'] = ['message' => $fault->message];
            if ($faultDetail = $fault->getDetail()) {
                $data['Fault']['Detail'] = $faultDetail;
            }
        } else {
            $operationResultKey = "${operation}Result";
            $data[$operationResponseKey][$operationResultKey] = $this->result; 
        }
        return $data;
    }

    /**
     * @return Psr\Http\Message\ResponseInterface
     */
    public function toServerResponse() : Psr\Http\Message\ResponseInterface
    {
        $data = $this->getData();
        // @todo: JSON
        $responseBody = json_encode($data);
        // @todo: Diactoros
        $response = new Zend\Diactoros\Response();
        $response->withBody($responseBody);
        return $response;
    }
}

class HttpServerResponse extends ServerResponse
{
    const STATUS_CODE_OK = 200;
    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_I_S_E = 500;
    const REASON_PHRASE_OK = 'OK';
    const REASON_PHRASE_BAD_REQUEST = 'Bad Request';
    const REASON_PHRASE_I_S_E = 'Internal Server Error';

    public function __construct(int $httpStatusCode = null, string $httpReasonPhrase = null)
    {
        parent::__construct();
        $this->httpStatusCode = $httpStatusCode;
        $this->httpReasonPhrase = $httpReasonPhrase;
    }

    /**
     * @var int
     */
    protected $httpStatusCode;

    /**
     * @var string
     */
    protected $httpReasonPhrase;

    /**
     * @return array
     */
    protected function getData()
    {
        $data = parent::getData();
        $operationResponseKey = "${operation}Response";
        $data[$operationResponseKey]['statusCode'] = $this->httpStatusCode;
        $data[$operationResponseKey]['reasonPhrase'] = $this->httpReasonPhrase;
        return $data;
    }
}

class Fault
{
    public function __construct($message, $phpCode = null)
    {
        $this->message = $message;
        $this->phpCode = $phpCode;
    }

    /**
     * @var string
     */
    protected $message;

    /**
     * @var stdClass
     */
    protected $detail;

    /**
     * @return stdClass
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @var stdClass
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
    }
}

class PhpFault
{
    public function __construct($message, $phpCode = null)
    {
        parent::__construct($message);
        $this->phpCode = $phpCode;
    }

    /**
     * @var integer
     */
    protected $phpCode;

    public function getPhpCode()
    {
        return $this->phpCode;
    }
}

class MyService
{
    public function myOperation ($in) {
        return $in * 42;
    }
}

function myOperation ($in) {
    return $in * 42;
}

$request = new Request();

$requestBody = $request->getBody();

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

/* D */
function middlewareC ($serverRequest) {
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
}

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
