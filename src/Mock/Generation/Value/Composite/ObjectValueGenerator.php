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
use App\Mock\Parameters\Schema\Type\TypeInterface;

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

    public function generateValue(TypeInterface $type): ?object
    {
        if ($type->isNullable() && 0 === random_int(0, 1)) {
            $value = null;
        } else {
            $value = $this->generateObject($type);
        }

        return $value;
    }

    private function generateObject(ObjectType $type): object
    {
        $object = new \stdClass();

        /**
         * @var string
         * @var TypeInterface $propertyType
         */
        foreach ($type->properties as $propertyName => $propertyType) {
            if (!$propertyType->isWriteOnly()) {
                $object->{$propertyName} = $this->generateValueByType($propertyType);
            }
        }

        return $object;
    }

    private function generateValueByType(TypeInterface $type)
    {
        $propertyValueGenerator = $this->generatorLocator->getValueGenerator($type);

        return $propertyValueGenerator->generateValue($type);
    }
}
