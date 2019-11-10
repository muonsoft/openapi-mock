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
use App\Mock\Parameters\InvalidObject;
use App\OpenAPI\Parsing\EndpointContext;
use App\OpenAPI\Parsing\PathContext;
use App\OpenAPI\Parsing\PathParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Routing\UrlMatcherInterface;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
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

    /** @test */
    public function parsePointedSchema_validPathsSchema_endpointsParsedAndReturned(): void
    {
        $parser = $this->createPathParser();
        $expectedEndpoint = new Endpoint();
        $expectedEndpoint->urlMatcher = \Phake::mock(UrlMatcherInterface::class);
        $this->givenInternalParser_parsePointedSchema_returns(new EndpointParameterCollection());
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $pathContext = new PathContext(self::PATH);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer, $pathContext);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::HTTP_METHOD],
            $context
        );
        $this->assertValidEndpointContextWithoutParameters($context);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoint, $endpoints->first());
    }

    /** @test */
    public function parsePointedSchema_parsedEndpointIsInvalid_endpointIgnoredAndErrorReported(): void
    {
        $parser = $this->createPathParser();
        $expectedEndpoint = new InvalidObject('error');
        $this->givenInternalParser_parsePointedSchema_returns(new EndpointParameterCollection());
        $specification = new SpecificationAccessor(self::VALID_PATH_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $pathContext = new PathContext(self::PATH);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer, $pathContext);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::HTTP_METHOD],
            $context
        );
        $this->assertValidEndpointContextWithoutParameters($context);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Endpoint will be ignored because of error: error.',
            ['get']
        );
        $this->assertCount(0, $endpoints);
    }

    /** @test */
    public function parsePointedSchema_invalidEndpoint_errorReported(): void
    {
        $parser = $this->createPathParser();
        $specification = new SpecificationAccessor([
            'get' => 'invalid',
        ]);
        $pointer = new SpecificationPointer();
        $this->givenInternalParser_parsePointedSchema_returns(new EndpointParameterCollection());
        $pathContext = new PathContext(self::PATH);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer, $pathContext);

        $this->assertCount(0, $endpoints);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Empty or invalid endpoint schema',
            ['get']
        );
    }

    /** @test */
    public function parsePointedSchema_pathsSchemaWithCommonParameters_parametersAndEndpointsParsedAndReturned(): void
    {
        $parser = $this->createPathParser();
        $expectedParameters = new EndpointParameterCollection();
        $this->givenInternalParser_parsePointedSchema_returns($expectedParameters);
        $expectedEndpoint = new Endpoint();
        $expectedEndpoint->urlMatcher = \Phake::mock(UrlMatcherInterface::class);
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoint);
        $specification = new SpecificationAccessor(self::VALID_PATH_WITH_PARAMETERS_SCHEMA);
        $pointer = new SpecificationPointer();
        $pathContext = new PathContext(self::PATH);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer, $pathContext);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            [self::PARAMETERS_TAG]
        );
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::HTTP_METHOD],
            $context
        );
        $this->assertValidEndpointContextWithParameters($context, $expectedParameters);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoint, $endpoints->first());
    }

    private function createPathParser(): PathParser
    {
        return new PathParser($this->contextualParser, $this->internalParser, $this->errorHandler);
    }

    private function assertValidEndpointContextWithoutParameters(EndpointContext $context): void
    {
        $this->assertSame(self::PATH, $context->getPath());
        $this->assertSame(self::HTTP_METHOD, $context->getHttpMethod()->getValue());
        $this->assertCount(0, $context->getParameters());
    }

    private function assertValidEndpointContextWithParameters(EndpointContext $context, EndpointParameterCollection $parameters): void
    {
        $this->assertSame(self::PATH, $context->getPath());
        $this->assertSame(self::HTTP_METHOD, $context->getHttpMethod()->getValue());
        $this->assertSame($parameters, $context->getParameters());
    }
}
