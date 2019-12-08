<?php

#require_once(__DIR__ . '/../vendor/autoload.php');

class ReflectionBench
{
    public function benchReflection()
    {
        $operations = [];
        $reflectionClass = new ReflectionClass(Zubr\ServiceClientContext::class);
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $parametersNamed = [];
            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $reflectionNamedType = $reflectionParameter->getType();
                $parametersNamed[$reflectionParameter->getName()] = $reflectionNamedType ? $reflectionNamedType->getName() : null;
            }
            $operations[$reflectionMethod->getName()] = $parametersNamed;
        }
        return $operations;
    }

    protected $reflectionAsArray = [
        'SCC' => [
            'methods' => [
                ['name' => 'createDefault'],
                [
                    'name' => 'setHttpClient',
                    'parameters' => [
                        [
                            'name' => 'httpClient',
                            'type' => ['name' => 'HttpClient']
                        ]
                    ]
                ],
                ['name' => 'getHttpClient'],
            ]
        ]
    ];

    public function benchReflectionAsArray()
    {
        $operations = [];
        $reflectionClass = $this->reflectionAsArray['SCC'];
        foreach ($reflectionClass['methods'] as $reflectionMethod) {
            $parametersNamed = [];
            foreach ($reflectionMethod['parameters'] as $reflectionParameter) {
                $reflectionNamedType = isset($reflectionParameter['type']) ? $reflectionParameter['type'] : null;
                $parametersNamed[$reflectionParameter['name']] = $reflectionNamedType ? $reflectionNamedType['name'] : null;
            }
            $operations[$reflectionMethod['name']] = $parametersNamed;
        }
        return $operations;
    }

    public function benchReflectionFactory()
    {
        $operations = [];
        $reflectionClass = new ReflectionClass(Zubr\Common\Operation::class);
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $operation = Zubr\Common\Operation::fromReflection($reflectionMethod);
            $operations[$reflectionMethod->getName()] = $operation;
        }
        return $operations;
    }
}

#var_dump((new ReflectionBench)->benchReflectionFactory());die;
