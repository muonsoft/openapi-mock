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
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathCollectionParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $pathParser;

    /** @var ErrorHandlerInterface */
    private $errorHandler;

    public function __construct(
        ContextualParserInterface $pathParser,
        ErrorHandlerInterface $errorHandler
    ) {
        $this->pathParser = $pathParser;
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ContextMarkerInterface $context
    ): SpecificationObjectMarkerInterface {
        assert($context instanceof SpecificationContext);

        $paths = $specification->getSchema($pointer);

        $allEndpoints = [];

        foreach ($paths as $path => $pathSchema) {
            $pathPointer = $pointer->withPathElement($path);
            $isValid = $this->validateSchema($pathSchema, $pathPointer);

            if ($isValid) {
                $pathContext = new PathContext($path, $context->getServers());
                $allEndpoints[] = $this->pathParser->parsePointedSchema($specification, $pathPointer, $pathContext);
            }
        }

        return $this->mergeEndpoints($allEndpoints);
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

    private function mergeEndpoints(array $allEndpoints): EndpointCollection
    {
        $endpoints = new EndpointCollection();
        $endpoints = $endpoints->merge(...$allEndpoints);
        assert($endpoints instanceof EndpointCollection);

        return $endpoints;
    }
}
