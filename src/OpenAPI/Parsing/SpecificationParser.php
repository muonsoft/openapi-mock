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

use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockParametersCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SpecificationParser
{
    /** @var ContextualParserInterface */
    private $endpointParser;

    public function __construct(ContextualParserInterface $endpointParser)
    {
        $this->endpointParser = $endpointParser;
    }

    public function parseSpecification(SpecificationAccessor $specification): MockParametersCollection
    {
        $pointer = new SpecificationPointer();
        $this->validateSpecificationSchema($specification, $pointer);

        $collection = new MockParametersCollection();
        $pathsPointer = $pointer->withPathElement('paths');
        $paths = $specification->getSchema($pathsPointer);

        foreach ($paths as $path => $endpoints) {
            $pathPointer = $pathsPointer->withPathElement($path);
            $this->validateEndpointSpecificationAtPath($endpoints, $pathPointer);

            foreach ($endpoints as $httpMethod => $endpointSpecification) {
                $endpointPointer = $pathPointer->withPathElement($httpMethod);
                $this->validateEndpointSpecificationAtPath($endpointSpecification, $endpointPointer);

                /** @var MockParameters $mockParameters */
                $mockParameters = $this->endpointParser->parsePointedSchema($specification, $endpointPointer);
                $mockParameters->path = $path;
                $mockParameters->httpMethod = strtoupper($httpMethod);
                $collection->add($mockParameters);
            }
        }

        return $collection;
    }

    private function validateSpecificationSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): void
    {
        $schema = $specification->getSchema($pointer);

        if (!array_key_exists('openapi', $schema)) {
            throw new ParsingException(
                'Cannot detect OpenAPI specification version: tag "openapi" does not exist.',
                $pointer
            );
        }

        if (((int)$schema['openapi']) !== 3) {
            throw new ParsingException(
                'OpenAPI specification version is not supported. Supports only 3.*.',
                $pointer
            );
        }

        if (
            !array_key_exists('paths', $schema)
            || !\is_array($schema['paths'])
            || \count($schema['paths']) === 0
        ) {
            throw new ParsingException('Section "paths" is empty or does not exist', $pointer);
        }
    }

    private function validateEndpointSpecificationAtPath($endpointSpecification, SpecificationPointer $pointer): void
    {
        if (!\is_array($endpointSpecification) || \count($endpointSpecification) === 0) {
            throw new ParsingException('Empty or invalid endpoint specification', $pointer);
        }
    }
}
