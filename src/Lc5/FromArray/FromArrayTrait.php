<?php
declare(strict_types=1);

namespace Lc5\FromArray;

use InvalidArgumentException;
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
            $errorMessage = 'Errors encountered when constructing ' . self::class . "\n";
            $errorMessage .= !empty($missingProperties) ?
                'Missing properties: ' . rtrim(implode(', ', $missingProperties), ',') . "\n" : '';

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
                        if ($type === 'callable' && is_callable($propertyValue) ||
                            $type === 'iterable' && is_iterable($propertyValue) ||
                            //@todo check if array contains only specified types
                            substr($type, -2) === '[]' && is_array($propertyValue) ||
                            gettype($propertyValue) === $type ||
                            $propertyValue instanceof $type ) {
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
            $errorMessage = 'Errors encountered when constructing ' . self::class . "\n" . "Invalid properties: \n";

            foreach ($invalidProperties as $property) {
                $errorMessage .= " - " . $property['name'] . " must be of the type " . $property['expectedType'] . ", "
                    . $property['givenType'] . " given\n";
            }

            throw new InvalidArgumentException($errorMessage);
        }
    }

    protected static function createInstance(array $properties): self
    {
        $classProperties = array_keys(get_class_vars(self::class));

        $instance = new self();

        foreach ($classProperties as $propertyName) {
            $instance->$propertyName = $properties[$propertyName];
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
}
