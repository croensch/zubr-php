<?php

namespace Zubr;

class ServiceClient extends AbstractServiceClient
{
    /**
     * @var string
     */
    protected $serviceName;

    public function __construct(ServiceClientContext $context, string $serviceName)
    {
        parent::__construct($context);
        $this->serviceName = $serviceName;
    }

    public static function create(string $serviceName, ServiceClientContext $context = null) : self
    {
        if ($context === null) {
            $context = ServiceClientContext::createDefault();
        }
        return new static($context, $serviceName);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $operationName, array $parametersPositional)
    {
        // TODO parameters
        $result = $this->_call($this->serviceName, $operationName, $parametersPositional);
        return $result;
    }
}
