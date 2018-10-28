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
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayValueGenerator implements ValueGeneratorInterface
{
    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(ValueGeneratorLocator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    /**
     * @param ArrayType $type
     * @return array
     */
    public function generateValue(TypeMarkerInterface $type): array
    {
        $valueGenerator = $this->generatorLocator->getValueGenerator($type->items);

        $count = random_int(1, 20);

        $values = [];

        for ($i = 1; $i <= $count; $i++) {
            $values[] = $valueGenerator->generateValue($type->items);
        }

        return $values;
    }
}
