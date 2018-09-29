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
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ObjectValueGenerator implements ValueGeneratorInterface
{
    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(ValueGeneratorLocator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    /**
     * @param ObjectType $type
     * @return array
     */
    public function generateValue(TypeMarkerInterface $type): array
    {
        $object = [];

        foreach ($type->properties as $propertyName => $propertyType) {
            $object[$propertyName] = $this->generateValueByType($propertyType);
        }

        return $object;
    }

    private function generateValueByType(TypeMarkerInterface $type)
    {
        $propertyValueGenerator = $this->generatorLocator->getValueGenerator($type);

        return $propertyValueGenerator->generateValue($type);
    }
}
