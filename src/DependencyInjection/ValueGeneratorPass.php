<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ValueGeneratorPass implements CompilerPassInterface
{
    public const VALUE_GENERATOR_TAG  = 'app.value_generator';

    private const VALUE_GENERATOR_SERVICE_LOCATOR = 'value_generator_service_locator';

    public function process(ContainerBuilder $container): void
    {
        if ($container->has(self::VALUE_GENERATOR_SERVICE_LOCATOR)) {
            $serviceLocatorDefinition = $container->findDefinition(self::VALUE_GENERATOR_SERVICE_LOCATOR);
            $serviceMap = $this->makeValueGeneratorServiceMap($container);
            $serviceLocatorDefinition->addArgument($serviceMap);
        }
    }

    private function makeValueGeneratorServiceMap(ContainerBuilder $container): array
    {
        $typeParserServices = $container->findTaggedServiceIds(self::VALUE_GENERATOR_TAG);

        $serviceMap = [];

        foreach (array_keys($typeParserServices) as $serviceId) {
            $serviceMap[$serviceId] = new Reference($serviceId);
        }

        return $serviceMap;
    }
}
