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
use App\Mock\Parameters\Servers;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SpecificationParser
{
    /** @var ParserInterface */
    private $serversParser;

    /** @var ContextualParserInterface */
    private $pathCollectionParser;

    public function __construct(ParserInterface $serversParser, ContextualParserInterface $pathCollectionParser)
    {
        $this->serversParser = $serversParser;
        $this->pathCollectionParser = $pathCollectionParser;
    }

    public function parseSpecification(SpecificationAccessor $specification): EndpointCollection
    {
        $pointer = new SpecificationPointer();
        $this->validateSpecificationSchema($specification, $pointer);

        $context = $this->createSpecificationContext($specification, $pointer);

        return $this->parseEndpointsFromPaths($specification, $pointer, $context);
    }

    private function validateSpecificationSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): void
    {
        $schema = $specification->getSchema($pointer);

        if (array_key_exists('swagger', $schema)) {
            throw new ParsingException(
                'Swagger specification is not supported. Supports only OpenAPI v3.*.',
                $pointer
            );
        }

        if (!array_key_exists('openapi', $schema)) {
            throw new ParsingException(
                'Cannot detect OpenAPI specification version: tag "openapi" does not exist.',
                $pointer
            );
        }

        if (3 !== ((int) $schema['openapi'])) {
            throw new ParsingException(
                'OpenAPI specification version is not supported. Supports only 3.*.',
                $pointer
            );
        }

        if (
            !array_key_exists('paths', $schema)
            || !\is_array($schema['paths'])
            || 0 === \count($schema['paths'])
        ) {
            throw new ParsingException('Section "paths" is empty or does not exist', $pointer);
        }
    }

    private function createSpecificationContext(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationContext
    {
        $servers = $this->serversParser->parsePointedSchema($specification, $pointer->withPathElement('servers'));
        assert($servers instanceof Servers);

        return new SpecificationContext($servers);
    }

    private function parseEndpointsFromPaths(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        SpecificationContext $context
    ): EndpointCollection {
        $pathsPointer = $pointer->withPathElement('paths');
        $endpoints = $this->pathCollectionParser->parsePointedSchema($specification, $pathsPointer, $context);
        \assert($endpoints instanceof EndpointCollection);

        return $endpoints;
    }
}
