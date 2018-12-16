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
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
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

    /** @var TypeMarkerInterface */
    private $valueType;

    public function __construct(Generator $faker, ValueGeneratorLocator $generatorLocator)
    {
        $this->faker = $faker;
        $this->generatorLocator = $generatorLocator;
    }

    /**
     * @param HashMapType $type
     * @return array
     */
    public function generateValue(TypeMarkerInterface $type): array
    {
        $this->initializeValueGenerator($type->value);

        $properties = [];

        $length = $this->generateRandomArrayLength($type);

        for ($i = 0; $i < $length; $i++) {
            $key = $this->faker->unique()->word();
            $properties[$key] = $this->valueGenerator->generateValue($type->value);
        }

        return $properties;
    }

    private function initializeValueGenerator(TypeMarkerInterface $type): void
    {
        $this->valueType = $type;
        $this->valueGenerator = $this->generatorLocator->getValueGenerator($this->valueType);
    }

    private function generateRandomArrayLength(HashMapType $type): int
    {
        $minItems = $type->minProperties > 0 ? $type->minProperties : self::DEFAULT_MIN_PROPERTIES;
        $maxItems = $type->maxProperties > 0 ? $type->maxProperties : self::DEFAULT_MAX_PROPERTIES;

        return random_int($minItems, $maxItems);
    }
}
