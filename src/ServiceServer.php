<?php

namespace Zubr;

class ServiceServer
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * TODO parameters
     */
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
