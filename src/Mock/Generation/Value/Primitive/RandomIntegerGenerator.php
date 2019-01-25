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
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RandomIntegerGenerator implements ValueGeneratorInterface
{
    public function generateValue(TypeInterface $type): ?int
    {
        if ($type->isNullable() && 0 === random_int(0, 1)) {
            $value = null;
        } else {
            $value = $this->generateIntegerValue($type);
        }

        return $value;
    }

    private function generateIntegerValue(IntegerType $type): int
    {
        $minimum = $type->minimum ?? 0;
        $maximum = $type->maximum ?? mt_getrandmax();

        if ($type->exclusiveMinimum) {
            $minimum++;
        }

        if ($type->exclusiveMaximum) {
            $maximum--;
        }

        $value = random_int($minimum, $maximum);

        if ($type->multipleOf) {
            $value -= $value % $type->multipleOf;
        }

        return $value;
    }
}
