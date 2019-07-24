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

use App\Mock\Generation\Value\Length\LengthGenerator;
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
    /** @var Generator */
    private $faker;

    /** @var LengthGenerator */
    private $lengthGenerator;

    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(Generator $faker, LengthGenerator $lengthGenerator, ValueGeneratorLocator $generatorLocator)
    {
        $this->faker = $faker;
        $this->lengthGenerator = $lengthGenerator;
        $this->generatorLocator = $generatorLocator;
    }

    public function generateValue(TypeInterface $type): ?object
    {
        if ($type->isNullable() && 0 === random_int(0, 1)) {
            $value = null;
        } else {
            $value = $this->generateHashMap($type);
        }

        return $value;
    }

    private function generateHashMap(HashMapType $type): object
    {
        $defaultProperties = $this->generateDefaultProperties($type);

        return $this->generateAndAppendRandomProperties($type, $defaultProperties);
    }

    private function generateDefaultProperties(HashMapType $type): object
    {
        $properties = new \stdClass();

        foreach ($type->required as $defaultPropertyName) {
            $defaultPropertyType = $type->properties[$defaultPropertyName];
            $valueGenerator = $this->generatorLocator->getValueGenerator($defaultPropertyType);
            $properties->{$defaultPropertyName} = $valueGenerator->generateValue($defaultPropertyType);
        }

        return $properties;
    }

    private function generateAndAppendRandomProperties(HashMapType $type, object $properties): object
    {
        $valueGenerator = $this->generatorLocator->getValueGenerator($type->value);
        $length = $this->lengthGenerator->generateLength($type->minProperties, $type->maxProperties);
        $count = \count(get_object_vars($properties));

        for ($i = $count; $i < $length->value; $i++) {
            $key = $this->faker->unique()->word();
            $properties->{$key} = $valueGenerator->generateValue($type->value);
        }

        return $properties;
    }
}
