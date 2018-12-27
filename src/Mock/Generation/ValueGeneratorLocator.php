<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation;

use App\Mock\Exception\MockGenerationException;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ValueGeneratorLocator
{
    /** @var ContainerInterface */
    private $container;

    /** @var string[] */
    private $valueGeneratorMap;

    public function __construct(ContainerInterface $container, array $valueGeneratorMap)
    {
        $this->container = $container;
        $this->valueGeneratorMap = $valueGeneratorMap;
    }

    public function getValueGenerator(TypeInterface $type): ValueGeneratorInterface
    {
        $typeClass = \get_class($type);

        if (!array_key_exists($typeClass, $this->valueGeneratorMap)) {
            throw new MockGenerationException(
                sprintf('Value generator for class "%s" not found.', $typeClass)
            );
        }

        $generatorServiceId = $this->valueGeneratorMap[$typeClass];

        return $this->container->get($generatorServiceId);
    }
}
