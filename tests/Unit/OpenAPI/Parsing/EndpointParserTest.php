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

use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockResponse;
use App\OpenAPI\Parsing\EndpointParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class EndpointParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const RESPONSE_SPECIFICATION = ['response_specification'];
    private const RESPONSE_STATUS_CODE = '200';
    private const VALID_ENDPOINT_SCHEMA = [
        'responses' => [
            self::RESPONSE_STATUS_CODE => self::RESPONSE_SPECIFICATION,
        ],
    ];
    private const VALID_ENDPOINT_SCHEMA_WITH_DEFAULT_RESPONSE = [
        'responses' => [
            'default' => self::RESPONSE_SPECIFICATION,
        ],
    ];
    private const ENDPOINT_SPECIFICATION_WITH_INVALID_STATUS_CODE = [
        'responses' => [
            'invalid' => [],
        ],
    ];
    private const ENDPOINT_SPECIFICATION_WITH_INVALID_RESPONSE_SPECIFICATION = [
        'responses' => [
            '200' => 'invalid',
        ],
    ];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_validResponseSpecification_mockParametersWithResponses(): void
    {
        $parser = $this->createEndpointParser();
        $expectedMockResponse = new MockResponse();
        $this->givenReferenceResolvingParser_resolveReferenceAndParsePointedSchema_returns($expectedMockResponse);
        $specification = new SpecificationAccessor(self::VALID_ENDPOINT_SCHEMA);

        /** @var MockParameters $mockParameters */
        $mockParameters = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContextualParser(
            $specification,
            ['responses', '200']
        );
        $this->assertCount(1, $mockParameters->responses);
        $this->assertSame([(int) self::RESPONSE_STATUS_CODE], $mockParameters->responses->getKeys());
        /** @var MockResponse $mockResponse */
        $mockResponse = $mockParameters->responses->first();
        $this->assertSame($expectedMockResponse, $mockResponse);
        $this->assertSame((int) self::RESPONSE_STATUS_CODE, $mockResponse->statusCode);
    }

    /** @test */
    public function parsePointedSchema_validResponseSpecificationWithDefaultResponse_mockParametersWithResponses(): void
    {
        $parser = $this->createEndpointParser();
        $expectedMockResponse = new MockResponse();
        $this->givenReferenceResolvingParser_resolveReferenceAndParsePointedSchema_returns($expectedMockResponse);
        $specification = new SpecificationAccessor(self::VALID_ENDPOINT_SCHEMA_WITH_DEFAULT_RESPONSE);

        /** @var MockParameters $mockParameters */
        $mockParameters = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContextualParser(
            $specification,
            ['responses', 'default']
        );
        $this->assertCount(1, $mockParameters->responses);
        $this->assertSame([MockResponse::DEFAULT_STATUS_CODE], $mockParameters->responses->getKeys());
        /** @var MockResponse $mockResponse */
        $mockResponse = $mockParameters->responses->first();
        $this->assertSame($expectedMockResponse, $mockResponse);
        $this->assertSame(MockResponse::DEFAULT_STATUS_CODE, $mockResponse->statusCode);
    }

    /** @test */
    public function parsePointedSchema_specificationWithInvalidStatusCode_errorReported(): void
    {
        $parser = $this->createEndpointParser();
        $specification = new SpecificationAccessor(self::ENDPOINT_SPECIFICATION_WITH_INVALID_STATUS_CODE);

        /** @var MockParameters $mockParameters */
        $mockParameters = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(0, $mockParameters->responses);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Invalid status code. Must be integer or "default".',
            'responses.invalid'
        );
    }

    /** @test */
    public function parsePointedSchema_invalidResponseSpecification_errorReported(): void
    {
        $parser = $this->createEndpointParser();
        $specification = new SpecificationAccessor(self::ENDPOINT_SPECIFICATION_WITH_INVALID_RESPONSE_SPECIFICATION);

        /** @var MockParameters $mockParameters */
        $mockParameters = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(0, $mockParameters->responses);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Invalid response specification.',
            'responses.200'
        );
    }

    private function createEndpointParser(): EndpointParser
    {
        return new EndpointParser($this->contextualParser, $this->resolvingParser, $this->errorHandler, new NullLogger());
    }
}
