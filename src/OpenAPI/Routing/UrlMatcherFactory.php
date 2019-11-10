<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Routing;

use App\Enum\EndpointParameterLocationEnum;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;
use App\OpenAPI\Parsing\SpecificationPointer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UrlMatcherFactory
{
    /** @var ServerPathMaker */
    private $serverPathMaker;

    /** @var ErrorHandlerInterface */
    private $errorHandler;

    public function __construct(ServerPathMaker $serverPathMaker, ErrorHandlerInterface $errorHandler)
    {
        $this->serverPathMaker = $serverPathMaker;
        $this->errorHandler = $errorHandler;
    }

    public function createUrlMatcher(Endpoint $endpoint, SpecificationPointer $pointer): UrlMatcherInterface
    {
        $serverPaths = $this->serverPathMaker->createServerPaths($endpoint->servers);
        $path = $this->injectSchemaPatternsIntoPath($endpoint, $pointer);
        $isValid = $this->validatePath($path, $pointer);

        if ($isValid) {
            $matcher = $this->createRegularExpressionMatcher($serverPaths, $path);
        } else {
            $matcher = new NullUrlMatcher();
        }

        return $matcher;
    }

    private function injectSchemaPatternsIntoPath(Endpoint $endpoint, SpecificationPointer $pointer): string
    {
        $path = $endpoint->path;

        /** @var EndpointParameter $parameter */
        foreach ($endpoint->parameters as $parameter) {
            if (EndpointParameterLocationEnum::PATH === $parameter->in->getValue()) {
                $path = $this->injectSchemaPatternIntoPath($parameter, $path, $pointer);
            }
        }

        return $path;
    }

    private function injectSchemaPatternIntoPath(EndpointParameter $parameter, string $path, SpecificationPointer $pointer): string
    {
        $search = '{'.$parameter->name.'}';

        if ($parameter->schema instanceof IntegerType) {
            $replace = '(-?\d*)';
        } elseif ($parameter->schema instanceof NumberType) {
            $replace = '(-?(?:\d+|\d*\.\d+))';
        } elseif ($parameter->schema instanceof StringType) {
            $replace = '([^\\\\/]*)';
        } else {
            $replace = '([^\\\\/]*)';

            $this->errorHandler->reportError(
                'Unsupported schema type for Parameter Object in path, must be one of: "string", "number", "integer".',
                $pointer
            );
        }

        return str_replace($search, $replace, $path);
    }

    private function createRegularExpressionMatcher(array $serverPaths, string $path): RegularExpressionUrlMatcher
    {
        $pattern = $path;

        if (1 === count($serverPaths)) {
            $pattern = $serverPaths[0].$pattern;
        } elseif (count($serverPaths) > 1) {
            $pattern = '('.implode('|', $serverPaths).')'.$pattern;
        }

        $pattern = str_replace('/', '\\/', $pattern);
        $pattern = sprintf('/^%s$/', $pattern);

        return new RegularExpressionUrlMatcher($pattern);
    }

    private function validatePath(string $path, SpecificationPointer $pointer): bool
    {
        $hasErrors = preg_match_all('/{.*?}/', $path, $matches);

        if ($hasErrors) {
            $this->errorHandler->reportError(
                sprintf('Path has unresolved path segments: %s.', implode(', ', $matches[0])),
                $pointer
            );
        }

        return !$hasErrors;
    }
}
