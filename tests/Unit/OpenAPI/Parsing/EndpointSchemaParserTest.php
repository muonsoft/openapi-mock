<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing;

use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\InvalidObject;
use App\Mock\Parameters\Servers;
use App\OpenAPI\Parsing\EndpointContext;
use App\OpenAPI\Parsing\EndpointSchemaContext;
use App\OpenAPI\Parsing\EndpointSchemaParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointSchemaParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const PATH = '/entity';
    private const HTTP_METHOD = 'get';
    private const ENDPOINT_SCHEMA = ['endpointSchema'];
    private const VALID_PATH_SCHEMA = [
        self::HTTP_METHOD => self::ENDPOINT_SCHEMA,
    ];
    private const SERVERS_TAG = 'servers';

    /** @test */
    public function parsePointedSchema_validPathSchemaWithoutServers_endpointsParsedWithGlobalServersAndReturned(): void
    {
        $parser = $this->createEndpointSchemaParser();
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $expectedEndpoint = new Endpoint();
        $this->givenInternalParser_parsePointedSchema_returns(new Servers());
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $schemaContext = $this->givenEndpointSchemaContext();

        /** @var Endpoint $endpoint */
        $endpoint = $parser->parseEndpoint($specification, $pointer, $schemaContext);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            [self::HTTP_METHOD, self::SERVERS_TAG]
        );
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::HTTP_METHOD],
            $context
        );
        $this->assertEndpointContextWithExpectedValues($context, $schemaContext->getServers(), $schemaContext->getParameters());
        $this->assertSame($expectedEndpoint, $endpoint);
    }

    /** @test */
    public function parsePointedSchema_validPathSchemaWithServers_endpointsParsedWithLocalServersAndReturned(): void
    {
        $parser = $this->createEndpointSchemaParser();
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $expectedEndpoint = new Endpoint();
        $endpointServers = new Servers();
        $endpointServers->urls->add('url');
        $this->givenInternalParser_parsePointedSchema_returns($endpointServers);
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $schemaContext = $this->givenEndpointSchemaContext();

        /** @var Endpoint $endpoint */
        $endpoint = $parser->parseEndpoint($specification, $pointer, $schemaContext);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            [self::HTTP_METHOD, self::SERVERS_TAG]
        );
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::HTTP_METHOD],
            $context
        );
        $this->assertEndpointContextWithExpectedValues($context, $endpointServers, $schemaContext->getParameters());
        $this->assertSame($expectedEndpoint, $endpoint);
    }

    /** @test */
    public function parsePointedSchema_parsedEndpointIsInvalid_endpointIgnoredAndErrorReported(): void
    {
        $parser = $this->createEndpointSchemaParser();
        $expectedEndpoint = new InvalidObject('error');
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenInternalParser_parsePointedSchema_returns(new Servers());
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $schemaContext = $this->givenEndpointSchemaContext();

        /** @var Endpoint $endpoint */
        $endpoint = $parser->parseEndpoint($specification, $pointer, $schemaContext);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::HTTP_METHOD],
            $context
        );
        $this->assertEndpointContextWithExpectedValues($context, $schemaContext->getServers(), $schemaContext->getParameters());
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Endpoint will be ignored because of error: error.',
            ['get']
        );
        $this->assertNull($endpoint);
    }

    /** @test */
    public function parsePointedSchema_invalidEndpoint_errorReported(): void
    {
        $parser = $this->createEndpointSchemaParser();
        $specification = new SpecificationAccessor([
            'get' => [],
        ]);
        $pointer = new SpecificationPointer();
        $schemaContext = $this->givenEndpointSchemaContext();

        /** @var Endpoint $endpoint */
        $endpoint = $parser->parseEndpoint($specification, $pointer, $schemaContext);

        $this->assertNull($endpoint);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Empty or invalid endpoint schema',
            ['get']
        );
    }

    private function createEndpointSchemaParser(): EndpointSchemaParser
    {
        return new EndpointSchemaParser($this->internalParser, $this->contextualParser, $this->errorHandler);
    }

    private function assertEndpointContextWithExpectedValues(
        EndpointContext $endpointContext,
        Servers $servers,
        EndpointParameterCollection $parameters
    ): void {
        $this->assertSame(self::PATH, $endpointContext->getPath());
        $this->assertSame(self::HTTP_METHOD, $endpointContext->getHttpMethod()->getValue());
        $this->assertSame($servers, $endpointContext->getServers());
        $this->assertSame($parameters, $endpointContext->getParameters());
    }

    private function givenEndpointSchemaContext(): EndpointSchemaContext
    {
        $parameters = new EndpointParameterCollection();
        $servers = new Servers();

        return new EndpointSchemaContext(self::PATH, self::HTTP_METHOD, $parameters, $servers);
    }
}
