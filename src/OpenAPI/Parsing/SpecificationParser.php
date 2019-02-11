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
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SpecificationParser
{
    /** @var ContextualParserInterface */
    private $endpointParser;

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ContextualParserInterface $endpointParser,
        ParsingErrorHandlerInterface $errorHandler,
        LoggerInterface $logger
    ) {
        $this->endpointParser = $endpointParser;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
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
            $isValid = $this->validateEndpointSpecificationAtPath($endpoints, $pathPointer);

            if ($isValid) {
                $this->parseEndpointList($specification, $collection, $pathPointer, $path, $endpoints);
            }
        }

        return $collection;
    }

    private function parseEndpointList(
        SpecificationAccessor $specification,
        MockParametersCollection $collection,
        SpecificationPointer $pathPointer,
        $path,
        $endpoints
    ): void {
        foreach ($endpoints as $httpMethod => $endpointSpecification) {
            $endpointPointer = $pathPointer->withPathElement($httpMethod);
            $isValid = $this->validateEndpointSpecificationAtPath($endpointSpecification, $endpointPointer);

            if ($isValid) {
                $this->parseEndpoint($specification, $collection, $endpointPointer, $path, $httpMethod);
            }
        }
    }

    private function parseEndpoint(
        SpecificationAccessor $specification,
        MockParametersCollection $collection,
        SpecificationPointer $endpointPointer,
        $path,
        $httpMethod
    ): void {
        /** @var MockParameters $mockParameters */
        $mockParameters = $this->endpointParser->parsePointedSchema($specification, $endpointPointer);
        $mockParameters->path = $path;
        $mockParameters->httpMethod = strtoupper($httpMethod);
        $collection->add($mockParameters);

        $this->logger->debug(
            sprintf(
                'Endpoint with method "%s" and path "%s" was successfully parsed.',
                $mockParameters->httpMethod,
                $mockParameters->path
            ),
            ['path' => $endpointPointer->getPath()]
        );
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

    private function validateEndpointSpecificationAtPath($endpointSpecification, SpecificationPointer $pointer): bool
    {
        $isValid = true;

        if (!\is_array($endpointSpecification) || 0 === \count($endpointSpecification)) {
            $isValid = false;
            $this->errorHandler->reportError('Empty or invalid endpoint specification', $pointer);
        } elseif (array_key_exists('$ref', $endpointSpecification)) {
            $isValid = false;
            $this->errorHandler->reportError('References on paths is not supported', $pointer);
        }

        return $isValid;
    }
}
