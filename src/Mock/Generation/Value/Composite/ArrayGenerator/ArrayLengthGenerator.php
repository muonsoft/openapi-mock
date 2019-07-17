<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Composite\ArrayGenerator;

use App\Mock\Parameters\Schema\Type\Composite\ArrayType;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayLengthGenerator
{
    private const DEFAULT_MIN_ITEMS = 1;
    private const DEFAULT_MAX_ITEMS = 20;

    public function generateArrayLength(ArrayType $type): ArrayLength
    {
        $minItems = $type->minItems > 0 ? $type->minItems : self::DEFAULT_MIN_ITEMS;
        $maxItems = $type->maxItems > 0 ? $type->maxItems : self::DEFAULT_MAX_ITEMS;

        $length = new ArrayLength();
        $length->value = random_int($minItems, $maxItems);
        $length->minValue = $minItems;

        return $length;
    }
}
