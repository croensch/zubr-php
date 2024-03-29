<?php

namespace Zubr;

class Response
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
     * @var Request
     */
    protected $request;

    public function __construct(string $serviceName, string $operatioName)
    {
        $this->serviceName = $serviceName;
        $this->operationName = $operatioName;
    }

    public function getServiceName() : string
    {
        return $this->serviceName;
    }

    public function getOperationName() : string
    {
        return $this->operationName;
    }

    /**
     * @var Fault
     */
    protected $fault;

    /**
     * @pvar mixed
     */
    protected $result;

    /*public function getRequest() : ?Request
    {
        return $this->request;
    }*/

    public function getFault() : ?Fault
    {
        return $this->fault;
    }

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
