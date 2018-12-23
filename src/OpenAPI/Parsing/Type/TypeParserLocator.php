<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type;

use App\OpenAPI\Parsing\ContextualParserInterface;
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

    public function __construct(ContainerInterface $container, array $typeParserMap)
    {
        $this->container = $container;
        $this->typeParserMap = $typeParserMap;
    }

    public function getTypeParser(string $type): ContextualParserInterface
    {
        if (!array_key_exists($type, $this->typeParserMap)) {
            throw new \DomainException(sprintf('Unrecognized schema type "%s".', $type));
        }

        $generatorServiceId = $this->typeParserMap[$type];

        return $this->container->get($generatorServiceId);
    }
}
