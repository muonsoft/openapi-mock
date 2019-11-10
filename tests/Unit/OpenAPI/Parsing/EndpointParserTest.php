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

use App\Enum\HttpMethodEnum;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\InvalidObject;
use App\Mock\Parameters\MockResponseMap;
use App\OpenAPI\Parsing\EndpointContext;
use App\OpenAPI\Parsing\EndpointParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Routing\NullUrlMatcher;
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
        $pointer = new SpecificationPointer();
        $expectedMockResponses = new MockResponseMap();
        $expectedEndpointParameter = new EndpointParameter();
        $expectedContextParameter = new EndpointParameter();
        $expectedParameters = new EndpointParameterCollection([$expectedEndpointParameter]);
        $this->givenInternalParser_parsePointedSchema_returns($expectedMockResponses, $expectedParameters);
        $specification = new SpecificationAccessor(self::VALID_ENDPOINT_SCHEMA);
        $context = $this->givenEndpointContext($expectedContextParameter);
        $urlMatcher = $this->givenUrlMatcherFactory_createUrlMatcher_returnsUrlMatcher();

        /** @var Endpoint $endpoint */
        $endpoint = $parser->parsePointedSchema($specification, $pointer, $context);

        $this->assertInstanceOf(Endpoint::class, $endpoint);
        $this->assertInternalParser_parsePointedSchema_wasCalledTwiceWithSpecificationAndPointerPaths(
            $specification,
            ['responses'],
            ['parameters']
        );
        $this->assertUrlMatcherFactory_createUrlMatcher_wasCalledOnceWithEndpointAndPointer($endpoint, $pointer);
        $this->assertSame($expectedMockResponses, $endpoint->responses);
        $this->assertSame($context->getPath(), $endpoint->path);
        $this->assertSame($context->getHttpMethod(), $endpoint->httpMethod);
        $this->assertSame($urlMatcher, $endpoint->urlMatcher);
        $this->assertCount(2, $endpoint->parameters);
        $this->assertContains($expectedEndpointParameter, $endpoint->parameters);
        $this->assertContains($expectedContextParameter, $endpoint->parameters);
    }

    /** @test */
    public function parsePointedSchema_endpointWithInvalidUrl_invalidObjectReturned(): void
    {
        $parser = $this->createEndpointParser();
        $pointer = new SpecificationPointer();
        $expectedMockResponses = new MockResponseMap();
        $expectedEndpointParameter = new EndpointParameter();
        $expectedContextParameter = new EndpointParameter();
        $expectedParameters = new EndpointParameterCollection([$expectedEndpointParameter]);
        $this->givenInternalParser_parsePointedSchema_returns($expectedMockResponses, $expectedParameters);
        $specification = new SpecificationAccessor(self::VALID_ENDPOINT_SCHEMA);
        $context = $this->givenEndpointContext($expectedContextParameter);
        $this->givenUrlMatcherFactory_createUrlMatcher_returnsUrlMatcher(new NullUrlMatcher());

        /** @var InvalidObject $endpoint */
        $endpoint = $parser->parsePointedSchema($specification, $pointer, $context);

        $this->assertInstanceOf(InvalidObject::class, $endpoint);
        $this->assertSame('endpoint has not parsable url', $endpoint->getError());
        $this->assertInternalParser_parsePointedSchema_wasCalledTwiceWithSpecificationAndPointerPaths(
            $specification,
            ['responses'],
            ['parameters']
        );
    }

    private function createEndpointParser(): EndpointParser
    {
        return new EndpointParser(
            $this->internalParser,
            $this->internalParser,
            $this->urlMatcherFactory,
            new NullLogger()
        );
    }

    private function givenEndpointContext(EndpointParameter $expectedContextParameter): EndpointContext
    {
        return new EndpointContext(
            'path',
            new HttpMethodEnum(HttpMethodEnum::TRACE),
            new EndpointParameterCollection([$expectedContextParameter])
        );
    }
}
