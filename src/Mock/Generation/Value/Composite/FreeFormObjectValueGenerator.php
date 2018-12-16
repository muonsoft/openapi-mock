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
use App\Mock\Parameters\Schema\Type\Composite\FreeFormObjectType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use Faker\Generator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class FreeFormObjectValueGenerator implements ValueGeneratorInterface
{
    private const DEFAULT_MIN_PROPERTIES = 1;
    private const DEFAULT_MAX_PROPERTIES = 20;

    /** @var Generator */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * @param FreeFormObjectType $type
     * @return array
     */
    public function generateValue(TypeMarkerInterface $type): array
    {
        $properties = [];

        $length = $this->generateRandomArrayLength($type);

        for ($i = 0; $i < $length; $i++) {
            $key = $this->faker->unique()->word();
            $properties[$key] = $this->faker->word();
        }

        return $properties;
    }

    private function generateRandomArrayLength(FreeFormObjectType $type): int
    {
        $minItems = $type->minProperties > 0 ? $type->minProperties : self::DEFAULT_MIN_PROPERTIES;
        $maxItems = $type->maxProperties > 0 ? $type->maxProperties : self::DEFAULT_MAX_PROPERTIES;

        return random_int($minItems, $maxItems);
    }
}
