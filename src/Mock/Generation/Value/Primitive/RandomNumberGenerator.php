<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Primitive;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RandomNumberGenerator implements ValueGeneratorInterface
{
    public function generateValue(TypeInterface $type): ?float
    {
        if ($type->isNullable() && 0 === random_int(0, 1)) {
            $value = null;
        } else {
            $value = $this->generateFloatValue($type);
        }

        return $value;
    }

    private function generateFloatValue(NumberType $type): float
    {
        $minimum = (float) ($type->minimum ?? -mt_getrandmax() / 2);
        $maximum = (float) ($type->maximum ?? mt_getrandmax() / 2);
        $range = $maximum - $minimum;

        $uniformValue = $this->uniformRandomValue($type->exclusiveMinimum, $type->exclusiveMaximum);

        $value = $uniformValue * $range + $minimum;

        if ($type->multipleOf) {
            $value = floor($value / $type->multipleOf) * $type->multipleOf;
        }

        return $value;
    }

    private function uniformRandomValue(bool $exclusiveMinimum = false, bool $exclusiveMaximum = false): float
    {
        $minimum = $exclusiveMinimum ? 1 : 0;
        $maximum = mt_getrandmax() - ($exclusiveMaximum ? 1 : 0);

        $value1 = (float) random_int($minimum, $maximum) / mt_getrandmax();
        $value2 = (float) random_int($minimum, $maximum) / mt_getrandmax();

        return $value1 * $value2;
    }
}
