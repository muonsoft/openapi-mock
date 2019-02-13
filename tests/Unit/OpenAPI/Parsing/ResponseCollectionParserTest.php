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

use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\MockResponseCollection;
use App\OpenAPI\Parsing\ResponseCollectionParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseCollectionParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const RESPONSE_SPECIFICATION = ['response_specification'];
    private const RESPONSE_STATUS_CODE = '200';
    private const VALID_RESPONSE_SCHEMA = [
        self::RESPONSE_STATUS_CODE => self::RESPONSE_SPECIFICATION,
    ];
    private const VALID_DEFAULT_RESPONSE_SCHEMA = [
        'default' => self::RESPONSE_SPECIFICATION,
    ];
    private const RESPONSE_SCHEMA_WITH_INVALID_STATUS_CODE = [
        'invalid' => [],
    ];
    private const RESPONSE_SCHEMA_INVALID_STRUCTURE = [
        '200' => 'invalid',
    ];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_validResponseSpecification_mockEndpointWithResponses(): void
    {
        $parser = $this->createResponseCollectionParser();
        $expectedMockResponse = new MockResponse();
        $this->givenReferenceResolvingParser_resolveReferenceAndParsePointedSchema_returns($expectedMockResponse);
        $specification = new SpecificationAccessor(self::VALID_RESPONSE_SCHEMA);

        /** @var MockResponseCollection $responses */
        $responses = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContextualParser(
            $specification,
            ['200']
        );
        $this->assertCount(1, $responses);
        $this->assertSame([(int) self::RESPONSE_STATUS_CODE], $responses->getKeys());
        /** @var MockResponse $mockResponse */
        $mockResponse = $responses->first();
        $this->assertSame($expectedMockResponse, $mockResponse);
        $this->assertSame((int) self::RESPONSE_STATUS_CODE, $mockResponse->statusCode);
    }

    /** @test */
    public function parsePointedSchema_validResponseSpecificationWithDefaultResponse_mockEndpointWithResponses(): void
    {
        $parser = $this->createResponseCollectionParser();
        $expectedMockResponse = new MockResponse();
        $this->givenReferenceResolvingParser_resolveReferenceAndParsePointedSchema_returns($expectedMockResponse);
        $specification = new SpecificationAccessor(self::VALID_DEFAULT_RESPONSE_SCHEMA);

        /** @var MockResponseCollection $responses */
        $responses = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContextualParser(
            $specification,
            ['default']
        );
        $this->assertCount(1, $responses);
        $this->assertSame([MockResponse::DEFAULT_STATUS_CODE], $responses->getKeys());
        /** @var MockResponse $mockResponse */
        $mockResponse = $responses->first();
        $this->assertSame($expectedMockResponse, $mockResponse);
        $this->assertSame(MockResponse::DEFAULT_STATUS_CODE, $mockResponse->statusCode);
    }

    /** @test */
    public function parsePointedSchema_specificationWithInvalidStatusCode_errorReported(): void
    {
        $parser = $this->createResponseCollectionParser();
        $specification = new SpecificationAccessor(self::RESPONSE_SCHEMA_WITH_INVALID_STATUS_CODE);

        /** @var MockResponseCollection $responses */
        $responses = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(0, $responses);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Invalid status code. Must be integer or "default".',
            'invalid'
        );
    }

    /** @test */
    public function parsePointedSchema_invalidResponseSpecification_errorReported(): void
    {
        $parser = $this->createResponseCollectionParser();
        $specification = new SpecificationAccessor(self::RESPONSE_SCHEMA_INVALID_STRUCTURE);

        /** @var MockResponseCollection $responses */
        $responses = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(0, $responses);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Invalid response specification.',
            '200'
        );
    }

    private function createResponseCollectionParser(): ResponseCollectionParser
    {
        return new ResponseCollectionParser($this->contextualParser, $this->resolvingParser, $this->errorHandler, new NullLogger());
    }
}
