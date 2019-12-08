<?php

namespace Zubr\Common;

class Parameter
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isOptional;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var string
     */
    protected $typeName;

    public function __construct(string $name, bool $isOptional)
    {
        $this->name = $name;
        $this->isOptional = $isOptional;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function isOptional() : bool
    {
        return $this->isOptional;
    }
    public function setDefaultValue($defaultValue)
    {
        if (!$this->isOptional) {
            throw new \BadMethodCallException("Parameter is not optional, can not set default value.");
        }
        $this->defaultValue = $defaultValue;
    }

    public function getDefaultValue()
    {
        if (!$this->isOptional) {
            throw new \BadMethodCallException("Parameter is not optional, can not get default value.");
        }
        return $this->defaultValue;
    }

    public function setTypeName(string $typeName)
    {
        $this->typeName = $typeName;
    }

    public function getTypeName() : ?string
    {
        return $this->typeName;
    }

    public static function fromReflection(\ReflectionParameter $reflectionParameter)
    {
        $isOptional = $reflectionParameter->isOptional();
        $parameter = new static(
            $reflectionParameter->getName(),
            $isOptional
        );
        if ($isOptional) {
            $parameter->setDefaultValue($reflectionParameter->getDefaultValue());
        }
        $type = $reflectionParameter->getType();
        if ($type instanceof \ReflectionNamedType) {
            $parameter->setTypeName($type->getName());
        }
        return $parameter;
    }
}