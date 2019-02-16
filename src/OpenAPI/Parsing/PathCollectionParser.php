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

use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathCollectionParser implements ParserInterface
{
    /** @var ContextualParserInterface */
    private $endpointParser;

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    public function __construct(
        ContextualParserInterface $endpointParser,
        ParsingErrorHandlerInterface $errorHandler
    ) {
        $this->endpointParser = $endpointParser;
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $context = new PathCollectionParserContext($specification);
        $paths = $specification->getSchema($pointer);

        foreach ($paths as $path => $endpoints) {
            $context->path = $path;
            $context->pathPointer = $pointer->withPathElement($path);
            $isValid = $this->validateEndpointSpecificationAtPath($endpoints, $context->pathPointer);

            if ($isValid) {
                $this->parseEndpointList($endpoints, $context);
            }
        }

        return $context->endpoints;
    }

    private function parseEndpointList(array $endpoints, PathCollectionParserContext $context): void
    {
        foreach ($endpoints as $httpMethod => $endpointSpecification) {
            $context->endpointPointer = $context->pathPointer->withPathElement($httpMethod);
            $isValid = $this->validateEndpointSpecificationAtPath($endpointSpecification, $context->endpointPointer);

            if ($isValid) {
                $this->parseEndpoint($httpMethod, $context);
            }
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

    private function parseEndpoint(string $httpMethod, PathCollectionParserContext $context): void
    {
        $endpointContext = new EndpointContext();
        $endpointContext->path = $context->path;
        $endpointContext->httpMethod = strtoupper($httpMethod);

        $endpoint = $this->endpointParser->parsePointedSchema(
            $context->specification,
            $context->endpointPointer,
            $endpointContext
        );

        $context->endpoints->add($endpoint);
    }
}
