<?php
declare(strict_types=1);

namespace Lc5\FromArray;

use ArrayObject;
use Lc5\FromArray\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class FromArrayTraitTest extends TestCase
{
    public function testFromArray_GivenCorrectProperties_ShouldCreateObject()
    {
        $properties = [
            'bool'       => true,
            'int'        => 2,
            'float'      => 3.5,
            'string'     => 'example string',
            'array'      => ['example array'],
            'typedArray' => [new stdClass(), new stdClass()],
            'object'     => new stdClass(),
            'callable'   => function () {},
            'iterable'   => new ArrayObject()
        ];

        $instance = TestClass::fromArray($properties);

        $this->assertInstanceOf(TestClass::class, $instance);
    }

    public function testFromArray_GivenCorrectPropertiesForMultiTypeProperty_ShouldCreateObject()
    {
        $this->assertInstanceOf(TestClass2::class, TestClass2::fromArray([
            'stringOrNull'     => 'string',
            'typedArrayOrNull' => []
        ]));

        $this->assertInstanceOf(TestClass2::class, TestClass2::fromArray([
            'stringOrNull'     => null,
            'typedArrayOrNull' => null
        ]));
    }

    public function testFromArray_GivenCorrectPropertiesWithoutRedundantCheck_ShouldCreateObject()
    {
        $properties = [
            'bool'       => true,
            'int'        => 2,
            'float'      => 3.5,
            'string'     => 'example string',
            'array'      => ['example array'],
            'typedArray' => [new stdClass(), new stdClass()],
            'object'     => new stdClass(),
            'callable'   => function () {},
            'iterable'   => new ArrayObject(),
            'redundant_1' => 'redundant',
            'redundant_2' => 'redundant'
        ];

        $instance = TestClass::fromArray($properties, Options::DEFAULT & ~Options::VALIDATE_REDUNDANT);

        $this->assertInstanceOf(TestClass::class, $instance);
    }

    public function testFromArray_GivenCorrectPropertiesWithoutMissingCheck_ShouldCreateObject()
    {
        $properties = [];

        $instance = TestClass2::fromArray($properties, Options::DEFAULT & ~Options::VALIDATE_MISSING);

        $this->assertInstanceOf(TestClass2::class, $instance);
    }

    public function testFromArray_GivenInvalidTypesWithoutTypeCheck_ShouldCreateObject()
    {
        $properties = [
            'bool'           => 1,
            'int'            => '2',
            'float'          => '3.5',
            'string'         => 'example string',
            'array'          => ['example array'],
            'typedArray'     => [new stdClass(), 'example', 1],
            'object'         => [],
            'callable'       => function () {},
            'iterable'       => new ArrayObject()
        ];

        $instance = TestClass::fromArray($properties, Options::DEFAULT & ~Options::VALIDATE_TYPES);

        $this->assertInstanceOf(TestClass::class, $instance);
    }

    public function testFromArray_GivenMissingProperties_ShouldThrowException()
    {
        $properties = [
            'bool'   => true,
            'int'    => 2,
            'float'  => 3.5,
            'string' => 'example string',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing properties: array, typedArray, object, callable, iterable');

        TestClass::fromArray($properties);
    }

    public function testFromArray_GivenRedundantProperties_ShouldThrowException()
    {
        $properties = [
            'bool'        => true,
            'int'         => 2,
            'float'       => 3.5,
            'string'      => 'example string',
            'array'       => ['example array'],
            'typedArray'  => [new stdClass(), new stdClass()],
            'object'      => new stdClass(),
            'callable'    => function () {},
            'iterable'    => new ArrayObject(),
            'redundant_1' => 'redundant',
            'redundant_2' => 'redundant'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redundant properties: redundant_1, redundant_2');

        TestClass::fromArray($properties);
    }

    public function testFromArray_GivenInvalidProperties_ShouldThrowException()
    {
        $properties = [
            'bool'           => 1,
            'int'            => '2',
            'float'          => '3.5',
            'string'         => 'example string',
            'array'          => ['example array'],
            'typedArray'     => [new stdClass(), 'example', 1],
            'object'         => [],
            'callable'       => function () {},
            'iterable'       => new ArrayObject(),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid properties:');
        $this->expectExceptionMessage('bool must be of the type bool, integer given');
        $this->expectExceptionMessage('int must be of the type int, string given');
        $this->expectExceptionMessage('float must be of the type float, string given');
        $this->expectExceptionMessage('object must be of the type object, array given');
        $this->expectExceptionMessage('typedArray must be of the type stdClass[], [string, integer] given');

        TestClass::fromArray($properties);
    }
}

class TestClass
{
    use FromArrayTrait;

    /** @var bool */
    private $bool;

    /** @var int */
    private $int;

    /** @var float */
    private $float;

    /** @var string */
    private $string;

    /** @var array */
    private $array;

    /** @var stdClass[] */
    private $typedArray;

    /** @var object */
    private $object;

    /** @var callable */
    private $callable;

    /** @var iterable */
    private $iterable;
}

class TestClass2
{
    use FromArrayTrait;

    /** @var string|null */
    private $stringOrNull;

    /** @var stdClass[]|null */
    private $typedArrayOrNull;
}
