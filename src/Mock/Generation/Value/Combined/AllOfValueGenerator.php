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
use App\Mock\Parameters\Schema\Type\Combined\AllOfType;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class AllOfValueGenerator implements ValueGeneratorInterface
{
    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(ValueGeneratorLocator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    public function generateValue(TypeInterface $type)
    {
        $values = $this->generateValues($type);

        if (0 === \count($values)) {
            $value = new \stdClass();
        } else {
            $value = (object) \array_merge(...$values);
        }

        return $value;
    }

    private function generateValues(AllOfType $type): array
    {
        $values = [];

        foreach ($type->types as $internalType) {
            $generator = $this->generatorLocator->getValueGenerator($internalType);
            $values[] = (array) $generator->generateValue($internalType);
        }

        return $values;
    }
}
