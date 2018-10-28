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
class TypeParserPass implements CompilerPassInterface
{
    public const TYPE_PARSER_TAG  = 'app.type_parser';

    private const TYPE_PARSER_SERVICE_LOCATOR = 'type_parser_service_locator';

    public function process(ContainerBuilder $container): void
    {
        if ($container->has(self::TYPE_PARSER_SERVICE_LOCATOR)) {
            $serviceLocatorDefinition = $container->findDefinition(self::TYPE_PARSER_SERVICE_LOCATOR);
            $serviceMap = $this->makeTypeParserServiceMap($container);
            $serviceLocatorDefinition->addArgument($serviceMap);
        }
    }

    private function makeTypeParserServiceMap(ContainerBuilder $container): array
    {
        $typeParserServices = $container->findTaggedServiceIds(self::TYPE_PARSER_TAG);

        $serviceMap = [];

        foreach (array_keys($typeParserServices) as $serviceId) {
            $serviceMap[$serviceId] = new Reference($serviceId);
        }

        return $serviceMap;
    }
}
