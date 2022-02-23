<?php

declare(strict_types=1);
/*
 * This file is part of the lc5/from-array package.
 *
 * (c) Łukasz Krzyszczak <lukasz.krzyszczak@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Lc5\FromArray;

use ArrayObject;
use Lc5\FromArray\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FromArrayTraitTest extends TestCase
{
    public function testFromArray_GivenCorrectProperties_ShouldCreateObject(): void
    {
        $properties = $this->getProperties();

        $instance = TestClass::fromArray($properties);

        $this->assertSame($properties['bool'], $instance->bool);
        $this->assertSame($properties['int'], $instance->int);
        $this->assertSame($properties['float'], $instance->float);
        $this->assertSame($properties['string'], $instance->string);
        $this->assertSame($properties['array'], $instance->array);
        $this->assertSame($properties['object'], $instance->object);
        $this->assertSame($properties['callable'], $instance->callable);
        $this->assertSame($properties['iterable'], $instance->iterable);
        $this->assertSame($properties['typedArray'], $instance->typedArray);
        $this->assertSame($properties['typedIterable'], $instance->typedIterable);
        $this->assertSame($properties['mixed'], $instance->mixed);
    }

    public function testFromArray_GivenCorrectPropertiesForUnionTypeProperty_ShouldCreateObject(): void
    {
        $instance1 = TestClass2::fromArray([
            'intOrFloat' => 2,
            'typedArrayOrNull' => []
        ]);

        $instance2 = TestClass2::fromArray([
            'intOrFloat' => 2.5,
            'typedArrayOrNull' => null
        ]);

        $this->assertSame(2, $instance1->intOrFloat);
        $this->assertSame([], $instance1->typedArrayOrNull);
        $this->assertSame(2.5, $instance2->intOrFloat);
        $this->assertNull($instance2->typedArrayOrNull);
    }

    public function testFromArray_GivenCorrectPropertiesWithoutRedundantCheck_ShouldCreateObject(): void
    {
        $properties = $this->getProperties();
        $properties += [
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
        $properties = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Missing properties: bool, int, float, string, array, object, callable, iterable, typedArray, ' .
            'typedIterable, mixed'
        );

        TestClass::fromArray($properties);
    }

    public function testFromArray_GivenRedundantProperties_ShouldThrowException(): void
    {
        $properties = $this->getProperties();
        $properties += [
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
            'callable' => 1,
            'typedArray' => [new stdClass(), 'example', 1],
            'typedIterable' => new ArrayObject([new stdClass(), 'example', 1])
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(<<<'MESSAGE'
Invalid properties:
 - callable must be of the type callable, integer given
 - typedArray must be of the type stdClass[], [string, integer] given
 - typedIterable must be of the type stdClass[], [string, integer] given
MESSAGE);

        TestClass::fromArray($properties, Options::VALIDATE_TYPES);
    }

    /**
     * @return mixed[]
     */
    private function getProperties(): array
    {
        return [
            'bool' => true,
            'int' => 2,
            'float' => 3.5,
            'string' => 'example string',
            'array' => ['example array'],
            'object' => new stdClass(),
            'callable' => function (): void {
            },
            'iterable' => new ArrayObject(),
            'typedArray' => [new stdClass(), new stdClass()],
            'typedIterable' => new ArrayObject([new stdClass(), new stdClass()]),
            'mixed' => 'mixed'
        ];
    }
}

final class TestClass
{
    use FromArrayTrait;

    public bool $bool;

    public int $int;

    public float $float;

    public string $string;

    /** @var mixed[] */
    public array $array;

    public object $object;

    /** @var callable */
    public $callable;

    /** @var mixed[] */
    public iterable $iterable;

    /** @var stdClass[] */
    public array $typedArray;

    /** @var stdClass[] */
    public iterable $typedIterable;

    /** @var mixed */
    public $mixed;
}

final class TestClass2
{
    use FromArrayTrait;

    /** @var int|float */
    public $intOrFloat;

    /** @var stdClass[]|null */
    public ?array $typedArrayOrNull;
}
