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

use Lc5\FromArray\Exception\InvalidArgumentException;
use ReflectionClass;

trait FromArrayTrait
{
    /**
     * @param mixed[] $properties
     */
    public static function fromArray(array $properties, int $options = Options::DEFAULT): self
    {
        self::validate($properties, $options);

        return self::createInstance($properties);
    }

    /**
     * @param mixed[] $properties
     */
    protected static function validate(array $properties, int $options): void
    {
        self::validateExistence($properties, $options);

        if ($options & Options::VALIDATE_TYPES) {
            self::validateTypes($properties);
        }
    }

    /**
     * @param mixed[] $properties
     */
    protected static function validateExistence(array $properties, int $options): void
    {
        $classProperties = array_keys(get_class_vars(self::class));

        $missingProperties = $options & Options::VALIDATE_MISSING ?
            array_diff($classProperties, array_keys($properties)) : [];

        $redundantProperties = $options & Options::VALIDATE_REDUNDANT ?
            array_diff(array_keys($properties), $classProperties) : [];

        if (!empty($missingProperties) || !empty($redundantProperties)) {
            $errorMessage = 'Errors encountered when constructing ' . self::class . PHP_EOL;

            $errorMessage .= empty($missingProperties) ?
                '' : 'Missing properties: ' . rtrim(implode(', ', $missingProperties), ',') . PHP_EOL;

            $errorMessage .= empty($redundantProperties) ?
                '' : 'Redundant properties: ' . rtrim(implode(', ', $redundantProperties), ',');

            throw new InvalidArgumentException($errorMessage);
        }
    }

    /**
     * @param mixed[] $properties
     */
    protected static function validateTypes(array $properties): void
    {
        $invalidProperties = [];

        $reflectionClass = new ReflectionClass(self::class);

        foreach ($reflectionClass->getProperties() as $refProperty) {
            if (preg_match('#@var\s+([\S]+)#', (string) $refProperty->getDocComment(), $matches)) {
                $types = array_map(static function (string $type): string {
                    return self::mapType($type);
                }, explode('|', $matches[1]));
                $propertyName = $refProperty->name;

                if (array_key_exists($propertyName, $properties)) {
                    $propertyValue = $properties[$propertyName];

                    foreach ($types as $type) {
                        if (substr($type, -2) === '[]' && is_iterable($propertyValue)) {
                            $invalidTypes = self::validateTypedIterable($propertyValue, substr($type, 0, -2));
                            if (!empty($invalidTypes)) {
                                $invalidProperties[] = [
                                    'name' => $propertyName,
                                    'expectedType' => $matches[1],
                                    'givenType' => '[' . implode(', ', array_unique($invalidTypes)) . ']'
                                ];
                            }
                            continue 2;
                        } elseif (self::validateType($type, $propertyValue)) {
                            continue 2;
                        }
                    }

                    $invalidProperties[] = [
                        'name' => $propertyName,
                        'expectedType' => $matches[1],
                        'givenType' => gettype($propertyValue)
                    ];
                }
            }
        }

        if (!empty($invalidProperties)) {
            $errorMessage = 'Errors encountered when constructing ' . self::class . PHP_EOL .
                            'Invalid properties:' . PHP_EOL;

            foreach ($invalidProperties as $invalidProperty) {
                $errorMessage .= ' - ' . $invalidProperty['name'] . ' must be of the type ' .
                    $invalidProperty['expectedType'] . ', ' . $invalidProperty['givenType'] . ' given' . PHP_EOL;
            }

            throw new InvalidArgumentException($errorMessage);
        }
    }

    /**
     * @param mixed[] $properties
     */
    protected static function createInstance(array $properties): self
    {
        $classProperties = array_keys(get_class_vars(self::class));

        $self = new self();

        foreach ($classProperties as $classProperty) {
            if (array_key_exists($classProperty, $properties)) {
                $self->{$classProperty} = $properties[$classProperty];
            }
        }

        return $self;
    }

    private static function mapType(string $type): string
    {
        $typesMap = [
            'bool' => 'boolean',
            'int' => 'integer',
            'float' => 'double',
            'resource (closed)' => 'resource',
            'null' => 'NULL'
        ];

        return $typesMap[$type] ?? $type;
    }

    /**
     * @param mixed $value
     */
    private static function validateType(string $type, $value): bool
    {
        return
            $type === 'mixed' ||
            ($type === 'callable' && is_callable($value)) ||
            gettype($value) === $type ||
            $value instanceof $type;
    }

    /**
     * @param mixed[] $typedArray
     * @return string[]
     */
    private static function validateTypedIterable(iterable $typedArray, string $type): array
    {
        $invalidTypes = [];

        foreach ($typedArray as $value) {
            if (!self::validateType($type, $value)) {
                $invalidTypes[] = gettype($value);
            }
        }

        return $invalidTypes;
    }
}
