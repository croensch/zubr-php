<?php

namespace Zubr;

class PhpFault extends Fault
{
    /**
     * @var int
     */
    protected $phpCode;

    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message);
        $this->phpCode = $code;
    }

    public function getCode() : int
    {
        return $this->phpCode;
    }
}
