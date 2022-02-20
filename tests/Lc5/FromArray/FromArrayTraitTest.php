<?php

declare(strict_types=1);

namespace Lc5\FromArray;

use ArrayObject;
use Lc5\FromArray\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FromArrayTraitTest extends TestCase
{
    public function testFromArray_GivenCorrectProperties_ShouldCreateObject(): void
    {
        $properties = [
            'bool' => true,
            'int' => 2,
            'float' => 3.5,
            'string' => 'example string',
            'array' => ['example array'],
            'typedArray' => [new stdClass(), new stdClass()],
            'object' => new stdClass(),
            'callable' => function (): void {
            },
            'iterable' => new ArrayObject()
        ];

        $instance = TestClass::fromArray($properties);

        $this->assertInstanceOf(TestClass::class, $instance);
    }

    public function testFromArray_GivenCorrectPropertiesForMultiTypeProperty_ShouldCreateObject(): void
    {
        $this->assertInstanceOf(TestClass2::class, TestClass2::fromArray([
            'typedArrayOrNull' => []
        ]));

        $this->assertInstanceOf(TestClass2::class, TestClass2::fromArray([
            'typedArrayOrNull' => null
        ]));
    }

    public function testFromArray_GivenCorrectPropertiesWithoutRedundantCheck_ShouldCreateObject(): void
    {
        $properties = [
            'bool' => true,
            'int' => 2,
            'float' => 3.5,
            'string' => 'example string',
            'array' => ['example array'],
            'typedArray' => [new stdClass(), new stdClass()],
            'object' => new stdClass(),
            'callable' => function (): void {
            },
            'iterable' => new ArrayObject(),
            'redundant_1' => 'redundant',
            'redundant_2' => 'redundant'
        ];

        $instance = TestClass::fromArray($properties, Options::DEFAULT & ~Options::VALIDATE_REDUNDANT);

        $this->assertInstanceOf(TestClass::class, $instance);
    }

    public function testFromArray_GivenCorrectPropertiesWithoutMissingCheck_ShouldCreateObject(): void
    {
        $properties = [];

        $instance = TestClass2::fromArray($properties, Options::DEFAULT & ~Options::VALIDATE_MISSING);

        $this->assertInstanceOf(TestClass2::class, $instance);
    }

    public function testFromArray_GivenMissingProperties_ShouldThrowException(): void
    {
        $properties = [
            'bool' => true,
            'int' => 2,
            'float' => 3.5,
            'string' => 'example string',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing properties: array, typedArray, object, callable, iterable');

        TestClass::fromArray($properties);
    }

    public function testFromArray_GivenRedundantProperties_ShouldThrowException(): void
    {
        $properties = [
            'bool' => true,
            'int' => 2,
            'float' => 3.5,
            'string' => 'example string',
            'array' => ['example array'],
            'typedArray' => [new stdClass(), new stdClass()],
            'object' => new stdClass(),
            'callable' => function (): void {
            },
            'iterable' => new ArrayObject(),
            'redundant_1' => 'redundant',
            'redundant_2' => 'redundant'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redundant properties: redundant_1, redundant_2');

        TestClass::fromArray($properties);
    }

    public function testFromArray_GivenInvalidProperties_ShouldThrowException(): void
    {
        $properties = [
            'typedArray' => [new stdClass(), 'example', 1],
            'callable' => 1
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid properties:');
        $this->expectExceptionMessage('typedArray must be of the type stdClass[], [string, integer] given');
        $this->expectExceptionMessage('callable must be of the type callable, integer given');

        TestClass::fromArray($properties, Options::VALIDATE_TYPES);
    }
}

final class TestClass
{
    use FromArrayTrait;

    private bool $bool;

    private int $int;

    private float $float;

    private string $string;

    private array $array;

    /** @var stdClass[] */
    private array $typedArray;

    private object $object;

    /** @var callable */
    private $callable;

    private iterable $iterable;
}

final class TestClass2
{
    use FromArrayTrait;

    /** @var stdClass[]|null */
    private ?array $typedArrayOrNull;
}
