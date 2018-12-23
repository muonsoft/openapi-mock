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
use App\Mock\Parameters\Schema\Type\Primitive\BooleanType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RandomBooleanGenerator implements ValueGeneratorInterface
{
    /**
     * @param BooleanType $type
     */
    public function generateValue(TypeMarkerInterface $type): ?bool
    {
        if ($type->nullable && random_int(0, 1) === 0) {
            $value = null;
        } else {
            $value = (bool)random_int(0, 1);
        }

        return $value;
    }
}
