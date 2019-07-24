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
use App\Mock\Parameters\Schema\Type\Composite\FreeFormObjectType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use Faker\Generator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class FreeFormObjectValueGenerator implements ValueGeneratorInterface
{
    /** @var Generator */
    private $faker;

    /** @var LengthGenerator */
    private $lengthGenerator;

    public function __construct(Generator $faker, LengthGenerator $lengthGenerator)
    {
        $this->faker = $faker;
        $this->lengthGenerator = $lengthGenerator;
    }

    public function generateValue(TypeInterface $type): ?object
    {
        if ($type->isNullable() && 0 === random_int(0, 1)) {
            $value = null;
        } else {
            $value = $this->generateObject($type);
        }

        return $value;
    }

    private function generateObject(FreeFormObjectType $type): object
    {
        $properties = new \stdClass();

        $length = $this->lengthGenerator->generateLength($type->minProperties, $type->maxProperties);

        for ($i = 0; $i < $length->value; $i++) {
            $key = $this->faker->unique()->word();
            $properties->{$key} = $this->faker->word();
        }

        return $properties;
    }
}
