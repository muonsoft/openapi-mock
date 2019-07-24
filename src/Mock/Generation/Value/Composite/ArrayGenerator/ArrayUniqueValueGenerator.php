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

use App\Mock\Generation\Value\Length\LengthGenerator;
use App\Mock\Generation\Value\Unique\UniqueValueGeneratorFactory;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayUniqueValueGenerator
{
    /** @var LengthGenerator */
    private $lengthGenerator;

    /** @var UniqueValueGeneratorFactory */
    private $uniqueValueGeneratorFactory;

    public function __construct(LengthGenerator $lengthGenerator, UniqueValueGeneratorFactory $uniqueValueGeneratorFactory)
    {
        $this->lengthGenerator = $lengthGenerator;
        $this->uniqueValueGeneratorFactory = $uniqueValueGeneratorFactory;
    }

    public function generateArray(ValueGeneratorInterface $generator, ArrayType $type): array
    {
        $uniqueGenerator = $this->uniqueValueGeneratorFactory->createGenerator($generator, $type->items);
        $length = $this->lengthGenerator->generateLength($type->minItems, $type->maxItems);

        $values = [];

        for ($i = 1; $i <= $length->value; $i++) {
            $value = $uniqueGenerator->nextValue();

            if ($uniqueGenerator->isAttemptsExceedingLimit()) {
                if ($i > $length->minValue) {
                    break;
                }

                throw new \RuntimeException('Cannot generate array with unique values, attempts limit exceeded');
            }

            $values[] = $value;
        }

        return $values;
    }
}
