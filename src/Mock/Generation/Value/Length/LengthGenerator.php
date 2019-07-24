<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Length;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class LengthGenerator
{
    private const DEFAULT_MAX_ITEMS = 20;

    public function generateLength(int $min, int $max): Length
    {
        $minItems = max($min, 0);
        $maxItems = $max > 0 ? $max : self::DEFAULT_MAX_ITEMS;
        $maxItems = max($minItems, $maxItems);

        $length = new Length();
        $length->value = random_int($minItems, $maxItems);
        $length->minValue = $minItems;

        return $length;
    }
}
