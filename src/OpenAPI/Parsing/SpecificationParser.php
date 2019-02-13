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

use App\Mock\Parameters\MockEndpoint;
use App\Mock\Parameters\MockEndpointCollection;
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

    public function parseSpecification(SpecificationAccessor $specification): MockEndpointCollection
    {
        $pointer = new SpecificationPointer();
        $this->validateSpecificationSchema($specification, $pointer);

        $context = new SpecificationParserContext($specification);
        $pathsPointer = $pointer->withPathElement('paths');
        $paths = $specification->getSchema($pathsPointer);

        foreach ($paths as $path => $endpoints) {
            $context->path = $path;
            $context->pathPointer = $pathsPointer->withPathElement($path);
            $isValid = $this->validateEndpointSpecificationAtPath($endpoints, $context->pathPointer);

            if ($isValid) {
                $this->parseEndpointList($endpoints, $context);
            }
        }

        return $context->mockEndpointCollection;
    }

    private function parseEndpointList(array $endpoints, SpecificationParserContext $context): void
    {
        foreach ($endpoints as $httpMethod => $endpointSpecification) {
            $context->endpointPointer = $context->pathPointer->withPathElement($httpMethod);
            $isValid = $this->validateEndpointSpecificationAtPath($endpointSpecification, $context->endpointPointer);

            if ($isValid) {
                $this->parseEndpoint($httpMethod, $context);
            }
        }
    }

    private function parseEndpoint(string $httpMethod, SpecificationParserContext $context): void
    {
        /** @var MockEndpoint $mockEndpoint */
        $mockEndpoint = $this->endpointParser->parsePointedSchema($context->specification, $context->endpointPointer);
        $mockEndpoint->path = $context->path;
        $mockEndpoint->httpMethod = strtoupper($httpMethod);

        $context->mockEndpointCollection->add($mockEndpoint);

        $this->logger->debug(
            sprintf(
                'Endpoint with method "%s" and path "%s" was successfully parsed.',
                $mockEndpoint->httpMethod,
                $mockEndpoint->path
            ),
            ['path' => $context->endpointPointer->getPath()]
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
