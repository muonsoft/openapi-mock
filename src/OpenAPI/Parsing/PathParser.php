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
use App\Mock\Parameters\EndpointCollection;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\InvalidObject;
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathParser implements ContextualParserInterface
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

    public function parsePointedSchema(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ContextMarkerInterface $context
    ): SpecificationObjectMarkerInterface {
        assert($context instanceof PathContext);

        $parameters = $this->parsePathParameters($specification, $pointer);
        $endpoints = new EndpointCollection();

        $pathSchema = $specification->getSchema($pointer);

        foreach ($pathSchema as $tag => $schema) {
            $httpMethod = strtolower($tag);
            $endpointPointer = $pointer->withPathElement($tag);

            if ($this->isValidEndpointSchema($schema, $httpMethod, $endpointPointer)) {
                $endpoint = $this->parseEndpoint(
                    $specification,
                    $endpointPointer,
                    new HttpMethodEnum($httpMethod),
                    $context->getPath(),
                    $parameters
                );

                if ($endpoint instanceof InvalidObject) {
                    $this->errorHandler->reportError(
                        sprintf('Endpoint will be ignored because of error: %s.', $endpoint->getError()),
                        $endpointPointer
                    );
                } else {
                    $endpoints->add($endpoint);
                }
            }
        }

        return $endpoints;
    }

    private function isValidEndpointSchema($schema, string $httpMethod, SpecificationPointer $pointer): bool
    {
        $isValid = HttpMethodEnum::isValid($httpMethod);

        if (!\is_array($schema) || 0 === \count($schema)) {
            $isValid = false;
            $this->errorHandler->reportError('Empty or invalid endpoint schema', $pointer);
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
    ): SpecificationObjectMarkerInterface {
        $endpointContext = new EndpointContext($path, $httpMethod, $pathParameters);

        return $this->endpointParser->parsePointedSchema(
            $specification,
            $endpointPointer,
            $endpointContext
        );
    }
}
