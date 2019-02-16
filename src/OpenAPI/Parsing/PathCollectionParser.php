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

use App\Mock\Parameters\Endpoint;
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathCollectionParser implements ParserInterface
{
    /** @var ParserInterface */
    private $endpointParser;

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ParserInterface $endpointParser,
        ParsingErrorHandlerInterface $errorHandler,
        LoggerInterface $logger
    ) {
        $this->endpointParser = $endpointParser;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
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

    private function parseEndpoint(string $httpMethod, PathCollectionParserContext $context): void
    {
        /** @var Endpoint $endpoint */
        $endpoint = $this->endpointParser->parsePointedSchema($context->specification, $context->endpointPointer);
        $endpoint->path = $context->path;
        $endpoint->httpMethod = strtoupper($httpMethod);

        $context->endpoints->add($endpoint);

        $this->logger->debug(
            sprintf(
                'Endpoint with method "%s" and path "%s" was successfully parsed.',
                $endpoint->httpMethod,
                $endpoint->path
            ),
            ['path' => $context->endpointPointer->getPath()]
        );
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
