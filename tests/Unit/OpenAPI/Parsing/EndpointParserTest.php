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
use App\OpenAPI\Parsing\EndpointParser;
use App\OpenAPI\Parsing\ResponseParser;
use PHPUnit\Framework\TestCase;

class EndpointParserTest extends TestCase
{
    private const RESPONSE_SPECIFICATION = ['response_specification'];
    private const RESPONSE_STATUS_CODE = '200';
    private const VALID_ENDPOINT_SPECIFICATION = [
        'responses' => [
            self::RESPONSE_STATUS_CODE => self::RESPONSE_SPECIFICATION
        ],
    ];
    private const ENDPOINT_SPECIFICATION_WITH_INVALID_STATUS_CODE = [
        'responses' => [
            'invalid' => []
        ],
    ];
    private const ENDPOINT_SPECIFICATION_WITH_INVALID_RESPONSE_SPECIFICATION = [
        'responses' => [
            '200' => 'invalid'
        ],
    ];

    /** @var ResponseParser */
    private $responseParser;

    protected function setUp(): void
    {
        $this->responseParser = \Phake::mock(ResponseParser::class);
    }

    /** @test */
    public function parseEndpoint_validResponseSpecification_mockParametersWithResponses(): void
    {
        $parser = $this->createEndpointParser();
        $expectedMockResponse = $this->givenResponseParser_parseResponse_returnsMockResponse();

        $mockParameters = $parser->parseEndpoint(self::VALID_ENDPOINT_SPECIFICATION);

        $this->assertResponseParser_parseResponse_isCalledOnceWithSpecification(self::RESPONSE_SPECIFICATION);
        $this->assertCount(1, $mockParameters->responses);
        $this->assertSame([(int) self::RESPONSE_STATUS_CODE], $mockParameters->responses->getKeys());
        /** @var MockResponse $mockResponse */
        $mockResponse = $mockParameters->responses->first();
        $this->assertSame($expectedMockResponse, $mockResponse);
        $this->assertSame((int) self::RESPONSE_STATUS_CODE, $mockResponse->statusCode);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid status code. Must be integer.
     */
    public function parseEndpoint_specificationWithInvalidStatusCode_exceptionThrown(): void
    {
        $parser = $this->createEndpointParser();

        $parser->parseEndpoint(self::ENDPOINT_SPECIFICATION_WITH_INVALID_STATUS_CODE);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid response specification
     */
    public function parseEndpoint_invalidResponseSpecification_exceptionThrown(): void
    {
        $parser = $this->createEndpointParser();

        $parser->parseEndpoint(self::ENDPOINT_SPECIFICATION_WITH_INVALID_RESPONSE_SPECIFICATION);
    }

    private function assertResponseParser_parseResponse_isCalledOnceWithSpecification(array $responseSpecification): void
    {
        \Phake::verify($this->responseParser)
            ->parseResponse($responseSpecification);
    }

    private function givenResponseParser_parseResponse_returnsMockResponse(): MockResponse
    {
        $expectedMockResponse = new MockResponse();

        \Phake::when($this->responseParser)
            ->parseResponse(\Phake::anyParameters())
            ->thenReturn($expectedMockResponse);

        return $expectedMockResponse;
    }

    private function createEndpointParser(): EndpointParser
    {
        return new EndpointParser($this->responseParser);
    }
}
