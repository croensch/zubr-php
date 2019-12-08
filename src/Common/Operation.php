<?php

namespace Zubr\Common;

class Operation
{
    const REGEX_TYPE_NAME = '/@param\s+([\w\\\]+\[\]?)\s+\$(\w+)/';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Parameter[]
     */
    protected $parameters;

    /**
     * @param Parameter[] $parameters
     */
    public function __construct(string $name, array $parameters = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return Parameter[]
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    public static function fromReflection(\ReflectionFunctionAbstract $reflectionFunction)
    {
        $parameters = [];
        $typeNames = null;
        foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
            $parameter = Parameter::fromReflection($reflectionParameter);
            $typeName = $parameter->getTypeName();
            if ($typeName === null || $typeName === 'array') {
                if ($typeNames === null) {
                    $matches = [];
                    $docComment = $reflectionFunction->getDocComment();
                    preg_match_all(static::REGEX_TYPE_NAME, $docComment, $matches);
                    $typeNames = array_combine($matches[2], $matches[1]);
                }
                $name = $parameter->getName();
                if (isset($typeNames[$name])) {
                    $typeName = $typeNames[$name];
                    $parameter->setTypeName($typeName);
                }
            }
            $parameters[$name] = $parameter;
        }
        $operation = new static(
            $reflectionFunction->getName(),
            $parameters
        );
        return $operation;
    }
}