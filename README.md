# FromArray

[![Build Status](https://github.com/Lc5/FromArray/workflows/Build/badge.svg)](https://github.com/Lc5/FromArray/actions)
[![Latest Stable Version](http://poser.pugx.org/lc5/from-array/v)](https://packagist.org/packages/lc5/from-array)
[![Total Downloads](http://poser.pugx.org/lc5/from-array/downloads)](https://packagist.org/packages/lc5/from-array)
[![PHP Version Require](http://poser.pugx.org/lc5/from-array/require/php)](https://packagist.org/packages/lc5/from-array)
[![License](http://poser.pugx.org/lc5/from-array/license)](https://packagist.org/packages/lc5/from-array)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://phpstan.org/)

Create objects from arrays with type checks.

## Installation
```
$ composer require lc5/from-array
```

## Usage
Add ```FromArrayTrait``` to the class you wish to be instantiated with the values from the array. It provides
```fromArray``` method, which will validate the data and create the object if the data is valid. Otherwise, either PHP 
```TypeError``` or ```Lc5\FromArray\Exception\InvalidArgumentException``` will be thrown.

The validation consists of the following steps:

- check if all the required properties are present
- check if there are no redundant properties
- check if all the properties have correct types according to the doc blocks. See 
[Supported Annotations](#supported-annotations)

Aforementioned behaviour can be configured. See [Options](#options)

### Basic example
```php
use Lc5\FromArray\FromArrayTrait;

class ExampleClass
{
    use FromArrayTrait;

    private bool $bool;
    private int $int;
    private float $float;
    private string $string;
    private array $array;
    private object $object;
}

$properties = [
    'bool' => true,
    'int' => 2,
    'float' => 3.5,
    'string' => 'example string',
    'array' => ['example array'],
    'object' => new stdClass()
];

$exampleObject = ExampleClass::fromArray($properties);
```

### Advanced example
```php
use Lc5\FromArray\FromArrayTrait;

class ExampleClass
{
    use FromArrayTrait;
    
    /** @var callable */
    private $callable;
    /** @var mixed[] */
    private iterable $iterable;
    /** @var stdClass[] */
    private array $typedArray;
    /** @var stdClass[] */
    private iterable $typedIterable;
    /** @var mixed */
    private $mixed;
    /** @var int|float */
    public $intOrFloat;
}

$properties = [
    'callable' => function (): void {},
    'iterable' => new ArrayObject(),
    'typedArray' => [new stdClass(), new stdClass()],
    'typedIterable' => new ArrayObject([new stdClass(), new stdClass()]),
    'mixed' => 'mixed',
    'intOrFloat' => 1.5
];

$exampleObject = ExampleClass::fromArray($properties);
```

## Docs

### Options

The following options are available:

- ```DEFAULT``` - check for missing and redundant properties and check types
- ```VALIDATE_MISSING``` - check for missing properties
- ```VALIDATE_REDUNDANT``` - check for redundant properties
- ```VALIDATE_TYPES``` - check types of properties

Options can be combined using bitwise operators. To disable validation of redundant properties in order to be able to
pass an array with more properties you can use the following code:

```php
ExampleClass::fromArray($properties, Options::DEFAULT & ~Options::VALIDATE_REDUNDANT);
```

More info: https://www.php.net/manual/en/language.operators.bitwise.php

### Supported Annotations

The following doc block annotations are supported:

* ```callable``` - standard PHP callable type
* ```mixed``` - represents PHP mixed typed, which basically means any type
* ```T[]``` - represents typed iterable of items of a given type e.g. ```int[]```, ```stdClass[]``` etc.
* union types - e.g. ```int|float``` - representing union of types

Standard PHP types are supported by native 
[Typed Properties](https://www.php.net/manual/en/language.oop5.properties.php)
