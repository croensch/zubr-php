<?php

namespace Zubr;

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

    public function __construct(string $serviceName, string $operationName, array $parametersNamed)
    {
        $this->serviceName = $serviceName;
        $this->operationName = $operationName;
        $this->parametersNamed = $parametersNamed;
    }

    public function getServiceName() : string
    {
        return $this->serviceName;
    }

    public function getOperationName() : string
    {
        return $this->operationName;
    }

    public function getParametersNamed() : array
    {
        return $this->parametersNamed;
    }
}
