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
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ContextualParserTestCaseTrait;
use PHPUnit\Framework\TestCase;

class EndpointParserTest extends TestCase
{
    use ContextualParserTestCaseTrait;

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

    protected function setUp(): void
    {
        $this->setUpContextualParser();
    }

    /** @test */
    public function parse_validResponseSpecification_mockParametersWithResponses(): void
    {
        $parser = $this->createEndpointParser();
        $expectedMockResponse = new MockResponse();
        $this->givenContextualParser_parsePointedSchema_returns($expectedMockResponse);

        $mockParameters = $parser->parsePointedSchema(self::VALID_ENDPOINT_SPECIFICATION, new SpecificationPointer());

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            self::RESPONSE_SPECIFICATION,
            'responses.200'
        );
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
    public function parse_specificationWithInvalidStatusCode_exceptionThrown(): void
    {
        $parser = $this->createEndpointParser();

        $parser->parsePointedSchema(self::ENDPOINT_SPECIFICATION_WITH_INVALID_STATUS_CODE, new SpecificationPointer());
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid response specification
     */
    public function parse_invalidResponseSpecification_exceptionThrown(): void
    {
        $parser = $this->createEndpointParser();

        $parser->parsePointedSchema(self::ENDPOINT_SPECIFICATION_WITH_INVALID_RESPONSE_SPECIFICATION, new SpecificationPointer());
    }

    private function createEndpointParser(): EndpointParser
    {
        return new EndpointParser($this->contextualParser);
    }
}
