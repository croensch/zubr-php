<?php

namespace Zubr;

abstract class AbstractServiceClient
{
    /**
     * @var ServiceClientContext
     */
    protected $context;

    public function __construct(ServiceClientContext $context)
    {
        $this->context = $context;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function _call(string $serviceName, string $operationName, array $parametersNamed)
    {
        $request = new Client\Request($serviceName, $operationName, $parametersNamed);
        $psrRequest = $request->toPsrRequest();
        // TODO Guzzle
        $httpClient = $this->context->getHttpClient();
        $psrResponse = $httpClient->sendRequest($psrRequest);
        $response = Client\Response::fromPsrResponse($request, $psrResponse);
        if ($fault = $response->getFault()) {
            if ($fault instanceof PhpFault) {
                throw new \Exception($fault->getMessage(), $fault->getCode());
            } else {
                throw new \Exception($fault->getMessage());
            }
        } else {
            return $response->getResult();
        }
    }
}
