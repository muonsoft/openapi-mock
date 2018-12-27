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

    /** @var ValueGeneratorInterface */
    private $valueGenerator;

    /** @var TypeInterface */
    private $valueType;

    /** @var array */
    private $uniqueValues;

    public function __construct(ValueGeneratorLocator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    public function generateValue(TypeInterface $type): ?array
    {
        if ($type->isNullable() && random_int(0, 1) === 0) {
            $value = null;
        } else {
            $value = $this->generateArray($type);
        }

        return $value;
    }

    private function generateArray(ArrayType $type): array
    {
        $this->initializeValueGenerator($type->items);

        $count = $this->generateRandomArrayLength($type);

        $values = [];

        for ($i = 1; $i <= $count; $i++) {
            $values[] = $this->generateArrayValue($type);
        }

        return $values;
    }

    private function generateRandomArrayLength(ArrayType $type): int
    {
        $minItems = $type->minItems > 0 ? $type->minItems : self::DEFAULT_MIN_ITEMS;
        $maxItems = $type->maxItems > 0 ? $type->maxItems : self::DEFAULT_MAX_ITEMS;

        return random_int($minItems, $maxItems);
    }

    private function initializeValueGenerator(TypeInterface $type): void
    {
        $this->valueType = $type;
        $this->valueGenerator = $this->generatorLocator->getValueGenerator($this->valueType);
        $this->uniqueValues = [];
    }

    private function generateArrayValue(ArrayType $type)
    {
        if ($type->uniqueItems) {
            $value = $this->generateUniqueValue($type->items);
        } else {
            $value = $this->valueGenerator->generateValue($type->items);
        }

        return $value;
    }

    private function generateUniqueValue(TypeInterface $itemsType)
    {
        $attempts = 0;

        do {
            $value = $this->valueGenerator->generateValue($itemsType);
            $attempts++;

            if ($attempts > self::MAX_ATTEMPTS) {
                throw new \RuntimeException('Cannot generate array with unique values, attempts limit exceeded');
            }
        } while (\in_array($value, $this->uniqueValues, true));

        $this->uniqueValues[] = $value;

        return $value;
    }
}
