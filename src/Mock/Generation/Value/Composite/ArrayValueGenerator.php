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

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Generation\ValueGeneratorLocator;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayValueGenerator implements ValueGeneratorInterface
{
    private const DEFAULT_MIN_ITEMS = 1;
    private const DEFAULT_MAX_ITEMS = 20;
    private const MAX_ATTEMPTS = 100;

    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(ValueGeneratorLocator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    public function generateValue(TypeInterface $type): ?array
    {
        if ($type->isNullable() && 0 === random_int(0, 1)) {
            $value = null;
        } else {
            $value = $this->generateArray($type);
        }

        return $value;
    }

    private function generateArray(ArrayType $type): array
    {
        $count = $this->generateRandomArrayLength($type);

        $values = [];
        $uniqueValues = [];
        $valueGenerator = $this->generatorLocator->getValueGenerator($type->items);

        for ($i = 1; $i <= $count; $i++) {
            try {
                $values[] = $this->generateArrayValue($valueGenerator, $type, $uniqueValues);
            } 
            catch (\RuntimeException $e) 
            {
                // Only throw attempts limit exception, of not enough values were generated
                if ($i < ($type->minItems > 0 ? $type->minItems : self::DEFAULT_MIN_ITEMS))
                {
                    throw $e;
                }
                else
                {
                    break;
                }
            }
        }

        return $values;
    }

    private function generateRandomArrayLength(ArrayType $type): int
    {
        $minItems = $type->minItems > 0 ? $type->minItems : self::DEFAULT_MIN_ITEMS;
        $maxItems = $type->maxItems > 0 ? $type->maxItems : self::DEFAULT_MAX_ITEMS;

        return random_int($minItems, $maxItems);
    }

    private function generateArrayValue(ValueGeneratorInterface $generator, ArrayType $type, array &$uniqueValues)
    {
        if ($type->uniqueItems) {
            $value = $this->generateUniqueValue($generator, $type->items, $uniqueValues);
        } else {
            $value = $generator->generateValue($type->items);
        }

        return $value;
    }

    private function generateUniqueValue(ValueGeneratorInterface $generator, TypeInterface $itemsType, array &$uniqueValues)
    {
        $attempts = 0;

        do {
            $value = $generator->generateValue($itemsType);
            $attempts++;

            if ($attempts > self::MAX_ATTEMPTS) {
                throw new \RuntimeException('Cannot generate array with unique values, attempts limit exceeded');
            }
        } while (\in_array($value, $uniqueValues, true));

        $uniqueValues[] = $value;

        return $value;
    }
}
