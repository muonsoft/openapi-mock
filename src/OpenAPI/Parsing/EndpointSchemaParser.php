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
use App\Mock\Parameters\InvalidObject;
use App\Mock\Parameters\Servers;
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointSchemaParser
{
    /** @var ParserInterface */
    private $serversParser;

    /** @var ContextualParserInterface */
    private $endpointParser;

    /** @var ErrorHandlerInterface */
    private $errorHandler;

    public function __construct(
        ParserInterface $serversParser,
        ContextualParserInterface $endpointParser,
        ErrorHandlerInterface $errorHandler
    ) {
        $this->serversParser = $serversParser;
        $this->endpointParser = $endpointParser;
        $this->errorHandler = $errorHandler;
    }

    public function parseEndpoint(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        EndpointSchemaContext $context
    ): ?Endpoint {
        $endpoint = null;

        $tag = $context->getTag();
        $httpMethod = strtolower($tag);
        $endpointPointer = $pointer->withPathElement($tag);
        $schema = $specification->getSchema($endpointPointer);

        if ($this->isValidEndpointSchema($schema, $httpMethod, $endpointPointer)) {
            $servers = $this->getServers($specification, $endpointPointer, $context);

            $endpointContext = new EndpointContext(
                $context->getPath(),
                new HttpMethodEnum($httpMethod),
                $context->getParameters(),
                $servers
            );

            $endpoint = $this->endpointParser->parsePointedSchema(
                $specification,
                $endpointPointer,
                $endpointContext
            );

            if ($endpoint instanceof InvalidObject) {
                $this->errorHandler->reportError(
                    sprintf('Endpoint will be ignored because of error: %s.', $endpoint->getError()),
                    $endpointPointer
                );

                $endpoint = null;
            }
        }

        assert(null === $endpoint || $endpoint instanceof Endpoint);

        return $endpoint;
    }

    private function isValidEndpointSchema(array $schema, string $httpMethod, SpecificationPointer $pointer): bool
    {
        $isValid = HttpMethodEnum::isValid($httpMethod);

        if (0 === \count($schema)) {
            $isValid = false;
            $this->errorHandler->reportError('Empty or invalid endpoint schema', $pointer);
        }

        return $isValid;
    }

    private function getServers(
        SpecificationAccessor $specification,
        SpecificationPointer $endpointPointer,
        EndpointSchemaContext $context
    ): Servers {
        $servers = $this->serversParser->parsePointedSchema($specification, $endpointPointer->withPathElement('servers'));
        assert($servers instanceof Servers);

        if (0 === $servers->urls->count()) {
            $servers = $context->getServers();
        }

        return $servers;
    }
}
