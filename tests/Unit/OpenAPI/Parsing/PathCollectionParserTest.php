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
use App\OpenAPI\Parsing\PathCollectionParser;
use App\OpenAPI\Parsing\PathContext;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
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
    private const VALID_PATHS_SCHEMA = [
        self::PATH => [
            self::HTTP_METHOD => self::ENDPOINT_SCHEMA,
        ],
    ];

    /** @test */
    public function parsePointedSchema_validPathsSchema_endpointsParsedAndReturned(): void
    {
        $parser = $this->createPathCollectionParser();
        $expectedEndpoints = new EndpointCollection([new Endpoint()]);
        $this->givenInternalParser_parsePointedSchema_returns(new EndpointParameterCollection());
        $specification = new SpecificationAccessor(self::VALID_PATHS_SCHEMA);
        $pointer = new SpecificationPointer();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoints);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            [self::PATH],
            $context
        );
        $this->assertValidPathContext($context);
        $this->assertCount(1, $endpoints);
        $this->assertSame($expectedEndpoints->toArray(), $endpoints->toArray());
    }

    /** @test */
    public function parsePointedSchema_emptyPathsSchema_emptyEndpointsReturned(): void
    {
        $parser = $this->createPathCollectionParser();
        $expectedEndpoints = new EndpointCollection([new Endpoint()]);
        $this->givenInternalParser_parsePointedSchema_returns(new EndpointParameterCollection());
        $specification = new SpecificationAccessor([]);
        $pointer = new SpecificationPointer();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoints);

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

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

    private function createPathCollectionParser(): PathCollectionParser
    {
        return new PathCollectionParser($this->contextualParser, $this->errorHandler);
    }

    private function assertValidPathContext(PathContext $context): void
    {
        $this->assertSame(self::PATH, $context->getPath());
    }
}
