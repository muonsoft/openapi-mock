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
use App\Mock\Generation\ValueGeneratorLocator;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class DelegatingArrayValueGenerator implements ValueGeneratorInterface
{
    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    /** @var ArrayRegularValueGenerator */
    private $regularValueGenerator;

    /** @var ArrayUniqueValueGenerator */
    private $uniqueValueGenerator;

    public function __construct(
        ValueGeneratorLocator $generatorLocator,
        ArrayRegularValueGenerator $regularValueGenerator,
        ArrayUniqueValueGenerator $uniqueValueGenerator
    ) {
        $this->generatorLocator = $generatorLocator;
        $this->regularValueGenerator = $regularValueGenerator;
        $this->uniqueValueGenerator = $uniqueValueGenerator;
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
        $valueGenerator = $this->generatorLocator->getValueGenerator($type->items);

        if ($type->uniqueItems) {
            $value = $this->uniqueValueGenerator->generateArray($valueGenerator, $type);
        } else {
            $value = $this->regularValueGenerator->generateArray($valueGenerator, $type);
        }

        return $value;
    }
}
