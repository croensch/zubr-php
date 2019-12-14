<?php

namespace Zubr;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ServiceServer implements RequestHandlerInterface, MiddlewareInterface
{
    /**
     * @var string
     */
    protected $serviceName;

    public function __construct(string $serviceName = null)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function call(string $operationName, array $parameters)
    {
        $serviceName = $this->serviceName;
        $serviceClassName = ucfirst($serviceName);
        $service = new $serviceClassName();
        $parametersPositional = array_values($parameters);
        $result = $service->$operationName(...$parametersPositional);
        return $result;
    }

    public function lambda(Server\Request $request) : Server\Response
    {
        if ($serviceName = $request->getServiceName()) {
            $this->serviceName = $serviceName;
        }
        $response = new Server\Response($request);
        try {
            $result = $this->call($request->getOperationName(), $request->getParametersNamed());
            $response->setResult($result, $request);
        } catch (\Exception $exception) {
            $fault = new PhpFault($exception->getMessage(), $exception->getCode());
            $response->setFault($fault);
            if (isset($exception->detail) && is_object($exception->detail) && $exception->detail instanceof \stdClass) {
                $fault->setDetail($exception->detail);
            }
        }
        return $response;
    }

    public function handleOrProcess(ServerRequestInterface $psrRequest, RequestHandlerInterface $handler = null) : ResponseInterface
    {
        $fault = null;
        try {
            $request = Server\Request::fromPsrServerRequest($psrRequest);
        } catch (\Exception $e) {
            $fault = new Fault($e->getMessage());
            $statusCode = Server\HttpResponse::STATUS_CODE_BAD_REQUEST;
            $reasonPhrase = Server\HttpResponse::REASON_PHRASE_BAD_REQUEST;
        }
        if ($fault === null) {
            $response = $this->lambda($request);
            $fault = $response->getFault();
            if ($fault === null) {
                $statusCode = Server\HttpResponse::STATUS_CODE_OK;
                $reasonPhrase = Server\HttpResponse::REASON_PHRASE_OK;
            } else {
                $statusCode = Server\HttpResponse::STATUS_CODE_I_S_E;
                $reasonPhrase = Server\HttpResponse::REASON_PHRASE_I_S_E;
            }
        }
        $httpResponse = new Server\HttpResponse(/*$request, */$statusCode, $reasonPhrase);
        if ($fault === null) {
            $result = $response->getResult();
            $httpResponse->setResult($result, $request);
        } else {
            $httpResponse->setFault($fault);
        }
        if ($handler === null) {
            $psrResponse = null;
        } else {
            $psrResponseFromHandler = $handler->handle($psrRequest);
        }
        try {
            $psrResponse = $httpResponse->toPsrResponse($psrResponse);
        } catch (\Exception $e) {
            $fault = new Fault($e->getMessage());
            $statusCode = Server\HttpResponse::STATUS_CODE_I_S_E;
            $reasonPhrase = Server\HttpResponse::REASON_PHRASE_I_S_E;
            $httpResponse = new Server\HttpResponse(/*$request, */$statusCode, $reasonPhrase);
            $httpResponse->setFault($fault);
            $psrResponse = $httpResponse->toPsrResponse($psrResponseFromHandler);
        }
        return $psrResponse;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->handleOrProcess($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return $this->handleOrProcess($request, $handler);
    }

    public function serve(ServerRequestInterface $psrRequest)
    {
        $psrResponse = $this->handleOrProcess($psrRequest);

        echo $psrResponse->getBody();
    }
}
