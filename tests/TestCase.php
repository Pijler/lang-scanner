<?php

namespace Tests;

use Illuminate\Support\Arr;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;
use ReflectionClass;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set protected / private property of a class.
     */
    public function setProperty(mixed $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionClass(get_class($object));

        $property = $reflection->getProperty($property);

        $property->setValue($object, $value);
    }

    /**
     * Call protected / private method of a class.
     */
    public function invokeMethod(mixed $object, string $method, mixed $parameters = null): mixed
    {
        $reflection = new ReflectionClass(get_class($object));

        $method = $reflection->getMethod($method);

        return $method->invokeArgs($object, Arr::wrap($parameters));
    }

    /**
     * Get protected / private property of a class.
     */
    public function invokeProperty(mixed $object, string $property): mixed
    {
        $reflection = new ReflectionClass(get_class($object));

        $property = $reflection->getProperty($property);

        return $property->getValue($object);
    }
}
