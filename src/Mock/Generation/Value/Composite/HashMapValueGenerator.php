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

    /** @var ValueGeneratorInterface */
    private $valueGenerator;

    /** @var TypeInterface */
    private $valueType;

    /** @var array */
    private $properties = [];

    public function __construct(Generator $faker, ValueGeneratorLocator $generatorLocator)
    {
        $this->faker = $faker;
        $this->generatorLocator = $generatorLocator;
    }

    /**
     * @param HashMapType $type
     * @return array
     */
    public function generateValue(TypeInterface $type): array
    {
        $this->initializeValueGenerator($type->value);

        $this->generateDefaultProperties($type);
        $this->generateRandomProperties($type);

        return $this->properties;
    }

    private function initializeValueGenerator(TypeInterface $type): void
    {
        $this->valueType = $type;
        $this->valueGenerator = $this->generatorLocator->getValueGenerator($this->valueType);
        $this->properties = [];
    }

    private function generateDefaultProperties(HashMapType $type): void
    {
        foreach ($type->required as $defaultPropertyName) {
            $defaultPropertyType = $type->properties[$defaultPropertyName];
            $valueGenerator = $this->generatorLocator->getValueGenerator($defaultPropertyType);
            $this->properties[$defaultPropertyName] = $valueGenerator->generateValue($defaultPropertyType);
        }
    }

    private function generateRandomProperties(HashMapType $type): array
    {
        $length = $this->generateRandomArrayLength($type);

        for ($i = \count($this->properties); $i < $length; $i++) {
            $key = $this->faker->unique()->word();
            $this->properties[$key] = $this->valueGenerator->generateValue($type->value);
        }

        return $this->properties;
    }

    private function generateRandomArrayLength(HashMapType $type): int
    {
        $minItems = $type->minProperties > 0 ? $type->minProperties : self::DEFAULT_MIN_PROPERTIES;
        $maxItems = $type->maxProperties > 0 ? $type->maxProperties : self::DEFAULT_MAX_PROPERTIES;

        return random_int($minItems, $maxItems);
    }
}
