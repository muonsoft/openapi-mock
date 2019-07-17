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

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayRegularValueGenerator
{
    /** @var ArrayLengthGenerator */
    private $lengthGenerator;

    public function __construct(ArrayLengthGenerator $lengthGenerator)
    {
        $this->lengthGenerator = $lengthGenerator;
    }

    public function generateArray(ValueGeneratorInterface $generator, ArrayType $type): array
    {
        $length = $this->lengthGenerator->generateArrayLength($type);

        $values = [];

        for ($i = 1; $i <= $length->value; $i++) {
            $values[] = $generator->generateValue($type->items);
        }

        return $values;
    }
}
