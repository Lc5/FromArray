# FromArray

[![Build Status](https://github.com/Lc5/FromArray/workflows/Build/badge.svg)](https://github.com/Lc5/FromArray/actions)
[![Latest Stable Version](http://poser.pugx.org/lc5/from-array/v)](https://packagist.org/packages/lc5/from-array)
[![Total Downloads](http://poser.pugx.org/lc5/from-array/downloads)](https://packagist.org/packages/lc5/from-array)
[![PHP Version Require](http://poser.pugx.org/lc5/from-array/require/php)](https://packagist.org/packages/lc5/from-array)
[![License](http://poser.pugx.org/lc5/from-array/license)](https://packagist.org/packages/lc5/from-array)
[![PHPStan Enabled](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://phpstan.org/)

Create objects from arrays with type checks

## Installation
```
$ composer install lc5/from-array
```

## Usage:
Add ```FromArrayTrait``` to the class you wish to be instantiated with the values from the array. It provides ```fromArray``` 
method, which will validate the data and create the object. The validation consists of the following steps:

- check if all the required properties are present
- check if there are no redundant properties
- check if all the properties have correct types according to the doc blocks

Aforementioned behaviour can be configured. See [Options](#options) 
  
```php
use Lc5\FromArray\FromArrayTrait

class ExampleClass
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

print_r(ExampleClass::fromArray($properties));

```
## Docs

### Options

The following options are available:

- **DEFAULT** - check for missing and redundant properties and check types     
- **VALIDATE_MISSING** - check for missing properties
- **VALIDATE_REDUNDANT** - check for redundant properties
- **VALIDATE_TYPES** - check types of properties

Options can be combined using bitwise operators. To disable validation of redundant properties in order to be able to
pass an array with more properties you can use the following code:

```php
ExampleClass::fromArray($properties, Options::DEFAULT & ~Options::VALIDATE_REDUNDANT);

```

More info: https://www.php.net/manual/en/language.operators.bitwise.php  

### Supported types

The following doc block annotations are supported:

* Ten PHP primitive types:

    * ```bool```
    * ```int```
    * ```float```
    * ```string```
    * ```array```
    * ```object```
    * ```callable```
    * ```iterable```
    * ```resource```
    * ```null```

* One additional type:

    * ```object[]``` - representing typed array of items of a given type eg. int[], stdClass[] etc.