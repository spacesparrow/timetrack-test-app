<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

trait ReflectionTrait
{
    /**
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws ReflectionException
     */
    protected function getNonPublicPropertyValue(object $object, string $property)
    {
        $property = new ReflectionProperty($object, $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param object $object
     * @param string $property
     * @param mixed $value
     * @throws ReflectionException
     */
    protected function setNonPublicPropertyValue(object $object, string $property, $value): void
    {
        $property = new ReflectionProperty($object, $property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * @param object $object
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws ReflectionException
     */
    protected function callNonPublicMethod(object $object, string $method, array $arguments)
    {
        $method = new ReflectionMethod($object, $method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}