<?php

namespace Zubr;

class Fault
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var object|null
     */
    protected $detail;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getDetail() : ?object
    {
        return $this->detail;
    }

    public function setDetail(object $detail)
    {
        $this->detail = $detail;
    }
}
