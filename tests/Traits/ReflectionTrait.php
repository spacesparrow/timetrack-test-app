<?php

declare(strict_types=1);

namespace App\Tests\Traits;

use ReflectionException;
use ReflectionProperty;

trait ReflectionTrait
{
    /**
     * @throws ReflectionException
     */
    protected function getPropertyValue(object $object, string $property)
    {
        $property = new ReflectionProperty($object, $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}