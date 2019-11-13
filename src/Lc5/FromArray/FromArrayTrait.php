<?php
declare(strict_types=1);

namespace Lc5\FromArray;

use Lc5\FromArray\Exception\InvalidArgumentException;
use ReflectionClass;

trait FromArrayTrait
{
    public static function fromArray(array $properties, int $options = Options::DEFAULT): self
    {
        self::validate($properties, $options);

        return self::createInstance($properties);
    }

    protected static function validate(array $properties, int $options): void
    {
        self::validateExistence($properties, $options);

        if ($options & Options::VALIDATE_TYPES) {
            self::validateTypes($properties);
        }
    }

    protected static function validateExistence(array $properties, int $options): void
    {
        $classProperties = array_keys(get_class_vars(self::class));

        $missingProperties = $options & Options::VALIDATE_MISSING ?
            array_diff($classProperties, array_keys($properties)) : [];

        $redundantProperties = $options & Options::VALIDATE_REDUNDANT ?
            array_diff(array_keys($properties), $classProperties) : [];

        if (!empty($missingProperties) || !empty($redundantProperties)) {
            $errorMessage = 'Errors encountered when constructing ' . self::class . PHP_EOL;

            $errorMessage .= !empty($missingProperties) ?
                'Missing properties: ' . rtrim(implode(', ', $missingProperties), ',') . PHP_EOL : '';

            $errorMessage .= !empty($redundantProperties) ?
                'Redundant properties: ' . rtrim(implode(', ', $redundantProperties), ',') : '';

            throw new InvalidArgumentException($errorMessage);
        }
    }

    protected static function validateTypes(array $properties): void
    {
        $invalidProperties = [];

        $refClass = new ReflectionClass(self::class);

        foreach ($refClass->getProperties() as $refProperty) {
            if (preg_match('/@var\s+([^\s]+)/', $refProperty->getDocComment(), $matches)) {
                $types = array_map([self::class, 'mapType'], explode('|', $matches[1]));
                $propertyName = $refProperty->name;

                if (array_key_exists($propertyName, $properties)) {
                    $propertyValue = $properties[$propertyName];

                    foreach ($types as $type) {
                        if (substr($type, -2) === '[]' && is_array($propertyValue)) {
                            $invalidTypes = self::validateTypedArray($propertyValue, substr($type, 0, -2));

                            if (!empty($invalidTypes)) {
                                $invalidProperties[] = [
                                    'name'         => $propertyName,
                                    'expectedType' => $matches[1],
                                    'givenType'    => '[' . implode(', ', array_unique($invalidTypes)) . ']'
                                ];
                            }
                            continue 2;
                        } else if (self::validateType($type, $propertyValue)) {
                            continue 2;
                        }
                    }

                    $invalidProperties[] = [
                        'name'         => $propertyName,
                        'expectedType' => $matches[1],
                        'givenType'    => gettype($propertyValue)
                    ];
                }
            }
        }

        if (!empty($invalidProperties)) {
            $errorMessage = 'Errors encountered when constructing ' . self::class . PHP_EOL .
                            'Invalid properties: ' . PHP_EOL;

            foreach ($invalidProperties as $property) {
                $errorMessage .= ' - ' . $property['name'] . ' must be of the type ' . $property['expectedType'] . ', '
                    . $property['givenType'] . ' given' . PHP_EOL;
            }

            throw new InvalidArgumentException($errorMessage);
        }
    }

    protected static function createInstance(array $properties): self
    {
        $classProperties = array_keys(get_class_vars(self::class));

        $instance = new self();

        foreach ($classProperties as $propertyName) {
            if (array_key_exists($propertyName, $properties)) {
                $instance->$propertyName = $properties[$propertyName];
            }
        }

        return $instance;
    }

    private static function mapType(string $type): string
    {
        $typesMap = [
            'bool'              => 'boolean',
            'int'               => 'integer',
            'float'             => 'double',
            'resource (closed)' => 'resource',
            'null'              => 'NULL'
        ];

        return $typesMap[$type] ?? $type;
    }

    private static function validateType(string $type, $value): bool
    {
        return
            $type === 'callable' && is_callable($value) ||
            $type === 'iterable' && is_iterable($value) ||
            gettype($value) === $type ||
            $value instanceof $type;
    }

    private static function validateTypedArray(array $typedArray, string $type): array
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
