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
use App\Mock\Parameters\EndpointCollection;
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
        $paths = $specification->getSchema($pointer);

        $totalEndpoints = [];

        foreach ($paths as $path => $pathSchema) {
            $pathPointer = $pointer->withPathElement($path);
            $isValid = $this->validateSchema($pathSchema, $pathPointer);

            if ($isValid) {
                $totalEndpoints[] = $this->parsePathEndpoints($specification, $pathPointer, $path);
            }
        }

        $endpoints = new EndpointCollection();

        return $endpoints->merge(...$totalEndpoints);
    }

    private function parsePathEndpoints(
        SpecificationAccessor $specification,
        SpecificationPointer $pathPointer,
        string $path
    ): EndpointCollection {
        $parameters = $this->parsePathParameters($specification, $pathPointer);
        $endpoints = new EndpointCollection();

        $pathSchema = $specification->getSchema($pathPointer);

        foreach ($pathSchema as $tag => $schema) {
            $httpMethod = strtolower($tag);
            $endpointPointer = $pathPointer->withPathElement($tag);
            $isValid = HttpMethodEnum::isValid($httpMethod) && $this->validateSchema($schema, $endpointPointer);

            if ($isValid) {
                $endpoint = $this->parseEndpoint(
                    $specification,
                    $endpointPointer,
                    new HttpMethodEnum($httpMethod),
                    $path,
                    $parameters
                );

                if ($endpoint->urlMatcher instanceof NullUrlMatcher) {
                    $this->errorHandler->reportError(
                        'Endpoint has invalid url matcher and is ignored.',
                        $endpointPointer
                    );
                } else {
                    $endpoints->add($endpoint);
                }
            }
        }

        return $endpoints;
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

    private function parsePathParameters(
        SpecificationAccessor $specification,
        SpecificationPointer $pathPointer
    ): EndpointParameterCollection {
        $pointer = $pathPointer->withPathElement('parameters');

        return $this->parameterCollectionParser->parsePointedSchema($specification, $pointer);
    }

    private function parseEndpoint(
        SpecificationAccessor $specification,
        SpecificationPointer $endpointPointer,
        HttpMethodEnum $httpMethod,
        string $path,
        EndpointParameterCollection $pathParameters
    ): Endpoint {
        $endpointContext = new EndpointContext();
        $endpointContext->path = $path;
        $endpointContext->httpMethod = $httpMethod;
        $endpointContext->parameters = $pathParameters;

        return $this->endpointParser->parsePointedSchema(
            $specification,
            $endpointPointer,
            $endpointContext
        );
    }
}
