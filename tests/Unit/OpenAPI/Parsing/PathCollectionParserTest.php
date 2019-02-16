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
use App\OpenAPI\Parsing\PathCollectionParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathCollectionParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const PATH = '/entity';
    private const HTTP_METHOD = 'get';
    private const ENDPOINT_SPECIFICATION = ['endpoint_specification'];
    private const VALID_PATHS_SCHEMA = [
        self::PATH => [
            self::HTTP_METHOD => self::ENDPOINT_SPECIFICATION,
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
        $this->givenInternalParser_parsePointedSchema_returns($expectedEndpoint);
        $specification = new SpecificationAccessor(self::VALID_PATHS_SCHEMA);
        $pointer = new SpecificationPointer();

        /** @var EndpointCollection $endpoints */
        $endpoints = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            [self::PATH, self::HTTP_METHOD]
        );
        $this->assertCount(1, $endpoints);
        /** @var Endpoint $mockEndpoint */
        $mockEndpoint = $endpoints->first();
        $this->assertSame($expectedEndpoint, $mockEndpoint);
        $this->assertSame(self::PATH, $mockEndpoint->path);
        $this->assertSame(strtoupper(self::HTTP_METHOD), $mockEndpoint->httpMethod);
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
            'Empty or invalid endpoint specification',
            '/entity'
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
            'Empty or invalid endpoint specification',
            '/entity.get'
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
            'References on paths is not supported',
            '/entity'
        );
    }

    private function createPathCollectionParser(): PathCollectionParser
    {
        return new PathCollectionParser($this->internalParser, $this->errorHandler, new NullLogger());
    }
}
