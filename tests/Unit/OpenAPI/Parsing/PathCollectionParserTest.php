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
use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\EndpointParameterCollection;
use App\OpenAPI\Parsing\EndpointContext;
use App\OpenAPI\Parsing\PathCollectionParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Routing\UrlMatcherInterface;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathCollectionParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const PATH = '/entity';
    private const HTTP_METHOD = 'get';
    private const ENDPOINT_SCHEMA = ['endpointSchema'];
    private const PARAMETERS_TAG = 'parameters';
    private const PARAMETERS_SCHEMA = ['parametersSchema'];
    private const VALID_PATHS_SCHEMA = [
        self::PATH => [
            self::HTTP_METHOD => self::ENDPOINT_SCHEMA,
        ],
    ];
    private const VALID_PATHS_WITH_PARAMETERS_SCHEMA = [
        self::PATH => [
            self::PARAMETERS_TAG => self::PARAMETERS_SCHEMA,
            self::HTTP_METHOD => self::ENDPOINT_SCHEMA,
        ],
    ];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_validPathsSchema_endpointsParsedAndReturned(): void
    {
        $parser = $this->createPathCollectionParser();
        $expectedEndpoint = new Endpoint();
        $expectedEndpoint->urlMatcher = \Phake::mock(UrlMatcherInterface::class);
        $this->givenInternalParser_parsePointedSchema_returns($expectedEndpoint);
        $specification = new SpecificationAccessor(self::VALID_PATHS_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::PATH, self::HTTP_METHOD],
            $context
        );
        $this->assertValidEndpointContextWithoutParameters($context);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoint, $endpoints->first());
    }

    /** @test */
    public function parsePointedSchema_parsedEndpointHasNullUrlMatcher_endpointIgnoredAndErrorReported(): void
    {
        $parser = $this->createPathCollectionParser();
        $expectedEndpoint = new Endpoint();
        $this->givenInternalParser_parsePointedSchema_returns($expectedEndpoint);
        $specification = new SpecificationAccessor(self::VALID_PATHS_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::PATH, self::HTTP_METHOD],
            $context
        );
        $this->assertValidEndpointContextWithoutParameters($context);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Endpoint has invalid url matcher and is ignored.',
            ['/entity', 'get']
        );
        $this->assertCount(0, $endpoints);
    }

    /** @test */
    public function parsePointedSchema_noEndpoints_errorReported(): void
    {
        $parser = $this->createPathCollectionParser();
        $specification = new SpecificationAccessor([
            '/entity' => 'invalid',
        ]);
        $pointer = new SpecificationPointer();

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertCount(0, $endpoints);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Empty or invalid path schema',
            ['/entity']
        );
    }

    /** @test */
    public function parsePointedSchema_invalidEndpoint_errorReported(): void
    {
        $parser = $this->createPathCollectionParser();
        $specification = new SpecificationAccessor([
            '/entity' => [
                'get' => 'invalid',
            ],
        ]);
        $pointer = new SpecificationPointer();

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertCount(0, $endpoints);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Empty or invalid path schema',
            ['/entity', 'get']
        );
    }

    /** @test */
    public function parsePointedSchema_pathWithReference_errorReported(): void
    {
        $parser = $this->createPathCollectionParser();
        $specification = new SpecificationAccessor([
            '/entity' => [
                '$ref' => 'anything',
            ],
        ]);
        $pointer = new SpecificationPointer();

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertCount(0, $endpoints);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'References on path is not supported',
            ['/entity']
        );
    }

    /** @test */
    public function parsePointedSchema_pathsSchemaWithCommonParameters_parametersAndEndpointsParsedAndReturned(): void
    {
        $parser = $this->createPathCollectionParser();
        $expectedParameters = new EndpointParameterCollection();
        $this->givenInternalParser_parsePointedSchema_returns($expectedParameters);
        $expectedEndpoint = new Endpoint();
        $expectedEndpoint->urlMatcher = \Phake::mock(UrlMatcherInterface::class);
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $specification = new SpecificationAccessor(self::VALID_PATHS_WITH_PARAMETERS_SCHEMA);
        $pointer = new SpecificationPointer();

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            [self::PATH, self::PARAMETERS_TAG]
        );
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::PATH, self::HTTP_METHOD],
            $context
        );
        $this->assertValidEndpointContextWithParameters($context, $expectedParameters);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoint, $endpoints->first());
    }

    /** @test */
    public function parsePointedSchema_secondPathWithoutParameters_noParametersForSecondEndpoint(): void
    {
        $parser = $this->createPathCollectionParser();
        $expectedParameters = new EndpointParameterCollection([new EndpointParameter()]);
        $this->givenInternalParser_parsePointedSchema_returns($expectedParameters);
        $expectedEndpoint = new Endpoint();
        $expectedEndpoint->urlMatcher = \Phake::mock(UrlMatcherInterface::class);
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $specification = new SpecificationAccessor([
            'parametersPath' => [
                self::PARAMETERS_TAG => self::PARAMETERS_SCHEMA,
            ],
            self::PATH => [
                self::HTTP_METHOD => self::ENDPOINT_SCHEMA,
            ],
        ]);
        $pointer = new SpecificationPointer();

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            ['parametersPath', self::PARAMETERS_TAG]
        );
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::PATH, self::HTTP_METHOD],
            $context
        );
        $this->assertValidEndpointContextWithoutParameters($context);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoint, $endpoints->first());
    }

    private function createPathCollectionParser(): PathCollectionParser
    {
        return new PathCollectionParser($this->contextualParser, $this->internalParser, $this->errorHandler);
    }

    private function assertValidEndpointContextWithoutParameters(EndpointContext $context): void
    {
        $this->assertSame(self::PATH, $context->path);
        $this->assertSame(self::HTTP_METHOD, $context->httpMethod->getValue());
        $this->assertCount(0, $context->parameters);
    }

    private function assertValidEndpointContextWithParameters(EndpointContext $context, EndpointParameterCollection $parameters): void
    {
        $this->assertSame(self::PATH, $context->path);
        $this->assertSame(self::HTTP_METHOD, $context->httpMethod->getValue());
        $this->assertSame($parameters, $context->parameters);
    }
}
