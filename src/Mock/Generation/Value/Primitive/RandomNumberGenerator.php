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
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RandomNumberGenerator implements ValueGeneratorInterface
{
    /**
     * @param NumberType $type
     */
    public function generateValue(TypeMarkerInterface $type): ?float
    {
        if ($type->nullable && random_int(0, 1) === 0) {
            $value = null;
        } else {
            $value = $this->generateFloatValue($type);
        }

        return $value;
    }

    private function generateFloatValue(NumberType $type): float
    {
        $minimum = $type->minimum ?? 0;
        $maximum = $type->maximum ?? mt_getrandmax();
        $range = $maximum - $minimum;

        $uniformValue = $this->uniformRandomValue($type->exclusiveMinimum, $type->exclusiveMaximum);

        $value = $uniformValue * $range + $minimum;

        if ($type->multipleOf) {
            $value = floor($value / $type->multipleOf) * $type->multipleOf;
        }

        return $value;
    }

    private function uniformRandomValue(bool $exclusiveMinimum, bool $exclusiveMaximum): float
    {
        $minimum = $exclusiveMinimum ? 1 : 0;
        $maximum = mt_getrandmax() - ($exclusiveMaximum ? 1 : 0);

        return ((float) random_int($minimum, $maximum)) / mt_getrandmax();
    }
}
