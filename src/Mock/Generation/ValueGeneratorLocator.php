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

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
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

    public function getValueGenerator(TypeMarkerInterface $type): ValueGeneratorInterface
    {
        $generatorServiceId = $this->valueGeneratorMap[\get_class($type)];

        return $this->container->get($generatorServiceId);
    }
}
