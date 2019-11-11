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
use App\Mock\Parameters\EndpointCollection;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\Servers;
use App\OpenAPI\Parsing\EndpointSchemaContext;
use App\OpenAPI\Parsing\EndpointSchemaParser;
use App\OpenAPI\Parsing\PathContext;
use App\OpenAPI\Parsing\PathParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const PATH = '/entity';
    private const HTTP_METHOD = 'get';
    private const ENDPOINT_SCHEMA = ['endpointSchema'];
    private const PARAMETERS_TAG = 'parameters';
    private const PARAMETERS_SCHEMA = ['parametersSchema'];
    private const VALID_PATH_SCHEMA = [
        self::HTTP_METHOD => self::ENDPOINT_SCHEMA,
    ];
    private const VALID_PATH_WITH_PARAMETERS_SCHEMA = [
        self::PARAMETERS_TAG => self::PARAMETERS_SCHEMA,
        self::HTTP_METHOD    => self::ENDPOINT_SCHEMA,
    ];
    private const SERVERS_TAG = 'servers';

    /** @var EndpointSchemaParser */
    private $endpointSchemaParser;

    protected function setUp(): void
    {
        $this->endpointSchemaParser = \Phake::mock(EndpointSchemaParser::class);
    }

    /** @test */
    public function parsePointedSchema_pathsSchemaWithoutServers_endpointsParsedWithGlobalServersAndReturned(): void
    {
        $parser = $this->createPathParser();
        $expectedEndpoint = new Endpoint();
        $parameters = new EndpointParameterCollection();
        $this->givenInternalParser_parsePointedSchema_returns($parameters, new Servers());
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenEndpointSchemaParser_parseEndpoint_returns($expectedEndpoint);
        $specificationServers = new Servers();
        $pathContext = new PathContext(self::PATH, $specificationServers);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer, $pathContext);

        $this->assertInternalParser_parsePointedSchema_wasCalledTwiceWithSpecificationAndPointerPaths(
            $specification,
            [self::PARAMETERS_TAG],
            [self::SERVERS_TAG]
        );
        $this->assertEndpointSchemaParser_parseEndpoint_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [],
            $context
        );
        $this->assertValidEndpointSchemaContext($context, $specificationServers, $parameters);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoint, $endpoints->first());
    }

    /** @test */
    public function parsePointedSchema_pathsSchemaWithServers_endpointsParsedWithLocalServersAndReturned(): void
    {
        $parser = $this->createPathParser();
        $expectedEndpoint = new Endpoint();
        $parameters = new EndpointParameterCollection();
        $pathServers = new Servers();
        $pathServers->urls->add('url');
        $this->givenInternalParser_parsePointedSchema_returns($parameters, $pathServers);
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenEndpointSchemaParser_parseEndpoint_returns($expectedEndpoint);
        $specificationServers = new Servers();
        $pathContext = new PathContext(self::PATH, $specificationServers);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer, $pathContext);

        $this->assertInternalParser_parsePointedSchema_wasCalledTwiceWithSpecificationAndPointerPaths(
            $specification,
            [self::PARAMETERS_TAG],
            [self::SERVERS_TAG]
        );
        $this->assertEndpointSchemaParser_parseEndpoint_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [],
            $context
        );
        $this->assertValidEndpointSchemaContext($context, $pathServers, $parameters);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoint, $endpoints->first());
    }

    /** @test */
    public function parsePointedSchema_noParsedEndpoint_endpointIgnored(): void
    {
        $parser = $this->createPathParser();
        $parameters = new EndpointParameterCollection();
        $this->givenInternalParser_parsePointedSchema_returns($parameters, new Servers());
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenEndpointSchemaParser_parseEndpoint_returns(null);
        $servers = new Servers();
        $pathContext = new PathContext(self::PATH, $servers);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer, $pathContext);

        $this->assertEndpointSchemaParser_parseEndpoint_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [],
            $context
        );
        $this->assertValidEndpointSchemaContext($context, $servers, $parameters);
        $this->assertCount(0, $endpoints);
    }

    private function createPathParser(): PathParser
    {
        return new PathParser($this->endpointSchemaParser, $this->internalParser, $this->internalParser);
    }

    private function assertValidEndpointSchemaContext(
        EndpointSchemaContext $context,
        Servers $servers,
        EndpointParameterCollection $parameters
    ): void {
        $this->assertSame(self::PATH, $context->getPath());
        $this->assertSame(self::HTTP_METHOD, $context->getTag());
        $this->assertSame($servers, $context->getServers());
        $this->assertSame($parameters, $context->getParameters());
    }

    private function givenEndpointSchemaParser_parseEndpoint_returns(Endpoint $endpoint = null): void
    {
        \Phake::when($this->endpointSchemaParser)
            ->parseEndpoint(\Phake::anyParameters())
            ->thenReturn($endpoint);
    }

    private function assertEndpointSchemaParser_parseEndpoint_wasCalledOnceWithSpecificationAndPointerPathAndContext(
        SpecificationAccessor $specification,
        array $path,
        &$context
    ): void {
        /* @var SpecificationPointer $pointer */
        \Phake::verify($this->endpointSchemaParser)
            ->parseEndpoint($specification, \Phake::capture($pointer), \Phake::capture($context));
        Assert::assertSame($path, $pointer->getPathElements());
    }
}
