<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Combined;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Generation\ValueGeneratorLocator;
use App\Mock\Parameters\Schema\Type\Combined\AnyOfType;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class AnyOfValueGenerator implements ValueGeneratorInterface
{
    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(ValueGeneratorLocator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    public function generateValue(TypeInterface $type): array
    {
        $values = $this->generateValues($type);

        return \array_merge(...$values);
    }

    private function generateValues(AnyOfType $type): array
    {
        $values = [];

        foreach ($type->types as $internalType) {
            if (random_int(0, 1) === 0) {
                $values[] = $this->generateValueOfType($internalType);
            }
        }

        if (\count($values) === 0) {
            $values[] = $this->generateOneOfValues($type);
        }

        return $values;
    }

    private function generateValueOfType(TypeInterface $type): array
    {
        $generator = $this->generatorLocator->getValueGenerator($type);

        return $generator->generateValue($type);
    }

    private function generateOneOfValues(AnyOfType $type): array
    {
        $randomInternalTypeIndex = random_int(0, $type->types->count() - 1);
        $randomInternalType = $type->types->get($randomInternalTypeIndex);

        return $this->generateValueOfType($randomInternalType);
    }
}
