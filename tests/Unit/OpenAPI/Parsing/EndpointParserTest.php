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
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
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
    public function parsePointedSchema_validResponseSpecification_mockParametersWithResponses(): void
    {
        $parser = $this->createEndpointParser();
        $expectedMockResponse = new MockResponse();
        $this->givenContextualParser_parsePointedSchema_returns($expectedMockResponse);
        $specification = new SpecificationAccessor(self::VALID_ENDPOINT_SPECIFICATION);

        /** @var MockParameters $mockParameters */
        $mockParameters = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
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
    public function parsePointedSchema_specificationWithInvalidStatusCode_exceptionThrown(): void
    {
        $parser = $this->createEndpointParser();
        $specification = new SpecificationAccessor(self::ENDPOINT_SPECIFICATION_WITH_INVALID_STATUS_CODE);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Invalid status code. Must be integer.');

        $parser->parsePointedSchema($specification, new SpecificationPointer());
    }

    /** @test */
    public function parsePointedSchema_invalidResponseSpecification_exceptionThrown(): void
    {
        $parser = $this->createEndpointParser();
        $specification = new SpecificationAccessor(self::ENDPOINT_SPECIFICATION_WITH_INVALID_RESPONSE_SPECIFICATION);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Invalid response specification');

        $parser->parsePointedSchema($specification, new SpecificationPointer());
    }

    private function createEndpointParser(): EndpointParser
    {
        return new EndpointParser($this->contextualParser);
    }
}
