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
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayUniqueValueGenerator
{
    private const MAX_ATTEMPTS = 100;

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
        $uniqueValues = [];

        for ($i = 1; $i <= $length->value; $i++) {
            [$value, $attemptsExceeded] = $this->generateUniqueValue($generator, $type->items, $uniqueValues);

            if ($attemptsExceeded) {
                if ($i > $length->minValue) {
                    break;
                }

                throw new \RuntimeException('Cannot generate array with unique values, attempts limit exceeded');
            }

            $values[] = $value;
        }

        return $values;
    }

    private function generateUniqueValue(ValueGeneratorInterface $generator, TypeInterface $itemsType, array &$uniqueValues): array
    {
        $attempts = 0;
        $attemptsExceeded = false;

        do {
            $value = $generator->generateValue($itemsType);
            $attempts++;

            if ($attempts > self::MAX_ATTEMPTS) {
                $attemptsExceeded = true;

                break;
            }
        } while (\in_array($value, $uniqueValues, true));

        $uniqueValues[] = $value;

        return [$value, $attemptsExceeded];
    }
}
