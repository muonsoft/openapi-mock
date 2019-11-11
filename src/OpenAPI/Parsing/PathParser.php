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

use App\Mock\Parameters\EndpointCollection;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\Servers;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathParser implements ContextualParserInterface
{
    /** @var EndpointSchemaParser */
    private $endpointSchemaParser;

    /** @var ParserInterface */
    private $serversParser;

    /** @var ParserInterface */
    private $parameterCollectionParser;

    public function __construct(
        EndpointSchemaParser $endpointSchemaParser,
        ParserInterface $serversParser,
        ParserInterface $parameterCollectionParser
    ) {
        $this->endpointSchemaParser = $endpointSchemaParser;
        $this->serversParser = $serversParser;
        $this->parameterCollectionParser = $parameterCollectionParser;
    }

    public function parsePointedSchema(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ContextMarkerInterface $context
    ): SpecificationObjectMarkerInterface {
        assert($context instanceof PathContext);

        $parameters = $this->parsePathParameters($specification, $pointer);
        $servers = $this->getServers($specification, $pointer, $context);

        $pathSchema = $specification->getSchema($pointer);
        $endpoints = new EndpointCollection();

        foreach (array_keys($pathSchema) as $tag) {
            $endpointsContext = new EndpointSchemaContext($context->getPath(), $tag, $parameters, $servers);
            $endpoint = $this->endpointSchemaParser->parseEndpoint($specification, $pointer, $endpointsContext);

            if ($endpoint) {
                $endpoints->add($endpoint);
            }
        }

        return $endpoints;
    }

    private function parsePathParameters(
        SpecificationAccessor $specification,
        SpecificationPointer $pathPointer
    ): EndpointParameterCollection {
        $pointer = $pathPointer->withPathElement('parameters');
        $parameters = $this->parameterCollectionParser->parsePointedSchema($specification, $pointer);
        assert($parameters instanceof EndpointParameterCollection);

        return $parameters;
    }

    private function getServers(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        PathContext $context
    ): Servers {
        $servers = $this->serversParser->parsePointedSchema($specification, $pointer->withPathElement('servers'));
        assert($servers instanceof Servers);

        if ($servers->urls->count() === 0) {
            $servers = $context->getServers();
        }

        return $servers;
    }
}
