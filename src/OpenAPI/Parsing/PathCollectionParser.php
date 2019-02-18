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

use App\Enum\HttpMethodEnum;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointParameterCollection;
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;
use App\OpenAPI\Routing\NullUrlMatcher;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathCollectionParser implements ParserInterface
{
    /** @var ContextualParserInterface */
    private $endpointParser;

    /** @var ParserInterface */
    private $parameterCollectionParser;

    /** @var ErrorHandlerInterface */
    private $errorHandler;

    public function __construct(
        ContextualParserInterface $endpointParser,
        ParserInterface $parameterCollectionParser,
        ErrorHandlerInterface $errorHandler
    ) {
        $this->endpointParser = $endpointParser;
        $this->parameterCollectionParser = $parameterCollectionParser;
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $context = new PathCollectionParserContext($specification);
        $paths = $specification->getSchema($pointer);

        foreach ($paths as $path => $pathSchema) {
            $context->path = $path;
            $context->pathPointer = $pointer->withPathElement($path);
            $isValid = $this->validateSchema($pathSchema, $context->pathPointer);

            if ($isValid) {
                $this->parsePathParameters($pathSchema, $context);
                $this->parsePathEndpoints($pathSchema, $context);
            }
        }

        return $context->endpoints;
    }

    private function parsePathParameters(array $pathSchema, PathCollectionParserContext $context): void
    {
        if (array_key_exists('parameters', $pathSchema)) {
            $pointer = $context->pathPointer->withPathElement('parameters');
            $context->pathParameters = $this->parameterCollectionParser->parsePointedSchema($context->specification, $pointer);
        } else {
            $context->pathParameters = new EndpointParameterCollection();
        }
    }

    private function parsePathEndpoints(array $pathSchema, PathCollectionParserContext $context): void
    {
        foreach ($pathSchema as $tag => $schema) {
            $httpMethod = strtolower($tag);
            $context->endpointPointer = $context->pathPointer->withPathElement($tag);
            $isValid = HttpMethodEnum::isValid($httpMethod) && $this->validateSchema($schema, $context->endpointPointer);

            if ($isValid) {
                $this->parseEndpoint(new HttpMethodEnum($httpMethod), $context);
            }
        }
    }

    private function validateSchema($schema, SpecificationPointer $pointer): bool
    {
        $isValid = true;

        if (!\is_array($schema) || 0 === \count($schema)) {
            $isValid = false;
            $this->errorHandler->reportError('Empty or invalid path schema', $pointer);
        } elseif (array_key_exists('$ref', $schema)) {
            $isValid = false;
            $this->errorHandler->reportError('References on path is not supported', $pointer);
        }

        return $isValid;
    }

    private function parseEndpoint(HttpMethodEnum $httpMethod, PathCollectionParserContext $context): void
    {
        $endpointContext = new EndpointContext();
        $endpointContext->path = $context->path;
        $endpointContext->httpMethod = $httpMethod;
        $endpointContext->parameters = $context->pathParameters;

        /** @var Endpoint $endpoint */
        $endpoint = $this->endpointParser->parsePointedSchema(
            $context->specification,
            $context->endpointPointer,
            $endpointContext
        );

        if ($endpoint->urlMatcher instanceof NullUrlMatcher) {
            $this->errorHandler->reportError(
                'Endpoint has invalid url matcher and is ignored.',
                $context->endpointPointer
            );
        } else {
            $context->endpoints->add($endpoint);
        }
    }
}
