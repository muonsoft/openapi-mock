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
use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\MockResponseCollection;
use App\OpenAPI\Parsing\EndpointContext;
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

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_validResponseSpecification_mockEndpointWithResponses(): void
    {
        $parser = $this->createEndpointParser();
        $expectedMockResponses = new MockResponseCollection();
        $expectedEndpointParameter = new EndpointParameter();
        $expectedContextParameter = new EndpointParameter();
        $expectedParameters = new EndpointParameterCollection([$expectedEndpointParameter]);
        $this->givenInternalParser_parsePointedSchema_returns($expectedMockResponses, $expectedParameters);
        $specification = new SpecificationAccessor(self::VALID_ENDPOINT_SCHEMA);
        $context = $this->givenEndpointContext($expectedContextParameter);

        /** @var Endpoint $endpoint */
        $endpoint = $parser->parsePointedSchema($specification, new SpecificationPointer(), $context);

        $this->assertInternalParser_parsePointedSchema_wasCalledTwiceWithSpecificationAndPointerPaths(
            $specification,
            ['responses'],
            ['parameters']
        );
        $this->assertSame($expectedMockResponses, $endpoint->responses);
        $this->assertSame($context->path, $endpoint->path);
        $this->assertSame($context->httpMethod, $endpoint->httpMethod);
        $this->assertCount(2, $endpoint->parameters);
        $this->assertContains($expectedEndpointParameter, $endpoint->parameters);
        $this->assertContains($expectedContextParameter, $endpoint->parameters);
    }

    private function createEndpointParser(): EndpointParser
    {
        return new EndpointParser($this->internalParser, $this->internalParser, new NullLogger());
    }

    private function givenEndpointContext(EndpointParameter $expectedContextParameter): EndpointContext
    {
        $context = new EndpointContext();
        $context->path = 'path';
        $context->httpMethod = 'HTTP_METHOD';
        $context->parameters->add($expectedContextParameter);

        return $context;
    }
}
