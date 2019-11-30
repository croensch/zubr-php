<?php

namespace Zubr;

class ServiceClient extends AbstractServiceClient
{
    /**
     * @var string
     */
    protected $serviceName;

    public function __construct(ServiceClientContext $context, $serviceName)
    {
        parent::__construct($context);
        $this->serviceName = $serviceName;
    }

    public static function create(string $serviceName, $context = null) : self
    {
        if ($context === null) {
            $context = ServiceClientContext::createDefault();
        }
        return new static($context, $serviceName);
    }

    /**
     * @param string $operationName
     * @param array $parametersPositional
     * @return mixed
     */
    public function __call($operationName, $parametersPositional)
    {
        // TODO parameters
        $result = $this->_call($this->serviceName, $operationName, $parametersPositional);
        return $result;
    }
}
