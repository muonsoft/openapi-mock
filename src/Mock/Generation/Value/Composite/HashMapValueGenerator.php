<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Composite;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Generation\ValueGeneratorLocator;
use App\Mock\Parameters\Schema\Type\Composite\HashMapType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use Faker\Generator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class HashMapValueGenerator implements ValueGeneratorInterface
{
    private const DEFAULT_MIN_PROPERTIES = 1;
    private const DEFAULT_MAX_PROPERTIES = 20;

    /** @var Generator */
    private $faker;

    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(Generator $faker, ValueGeneratorLocator $generatorLocator)
    {
        $this->faker = $faker;
        $this->generatorLocator = $generatorLocator;
    }

    public function generateValue(TypeInterface $type): ?array
    {
        if ($type->isNullable() && 0 === random_int(0, 1)) {
            $value = null;
        } else {
            $value = $this->generateHashMap($type);
        }

        return $value;
    }

    private function generateHashMap(HashMapType $type): array
    {
        $defaultProperties = $this->generateDefaultProperties($type);

        return $this->generateAndAppendRandomProperties($type, $defaultProperties);
    }

    private function generateDefaultProperties(HashMapType $type): array
    {
        $properties = [];

        foreach ($type->required as $defaultPropertyName) {
            $defaultPropertyType = $type->properties[$defaultPropertyName];
            $valueGenerator = $this->generatorLocator->getValueGenerator($defaultPropertyType);
            $properties[$defaultPropertyName] = $valueGenerator->generateValue($defaultPropertyType);
        }

        return $properties;
    }

    private function generateAndAppendRandomProperties(HashMapType $type, array $properties): array
    {
        $valueGenerator = $this->generatorLocator->getValueGenerator($type->value);
        $length = $this->generateRandomArrayLength($type);

        for ($i = \count($properties); $i < $length; $i++) {
            $key = $this->faker->unique()->word();
            $properties[$key] = $valueGenerator->generateValue($type->value);
        }

        return $properties;
    }

    private function generateRandomArrayLength(HashMapType $type): int
    {
        $minItems = $type->minProperties > 0 ? $type->minProperties : self::DEFAULT_MIN_PROPERTIES;
        $maxItems = $type->maxProperties > 0 ? $type->maxProperties : self::DEFAULT_MAX_PROPERTIES;

        return random_int($minItems, $maxItems);
    }
}
