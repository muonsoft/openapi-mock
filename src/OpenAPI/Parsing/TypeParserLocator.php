<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing;

use App\OpenAPI\Parsing\Type\TypeParserInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TypeParserLocator
{
    /** @var ContainerInterface */
    private $container;

    /** @var string[] */
    private $typeParserMap;

    public function __construct(ContainerInterface $container, array $valueGeneratorMap)
    {
        $this->container = $container;
        $this->typeParserMap = $valueGeneratorMap;
    }

    public function getTypeParser(string $type): TypeParserInterface
    {
        $generatorServiceId = $this->typeParserMap[$type];

        return $this->container->get($generatorServiceId);
    }
}
