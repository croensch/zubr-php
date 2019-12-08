<?php

namespace Zubr;

class ServiceServer
{
    /**
     * @var string
     */
    protected $serviceName;

    public function __construct(string $serviceName)
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
}
