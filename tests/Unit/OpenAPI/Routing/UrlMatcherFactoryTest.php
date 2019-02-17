<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Routing;

use App\Enum\EndpointParameterLocationEnum;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Routing\NullUrlMatcher;
use App\OpenAPI\Routing\RegularExpressionUrlMatcher;
use App\OpenAPI\Routing\UrlMatcherFactory;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UrlMatcherFactoryTest extends TestCase
{
    use ParsingTestCaseTrait;

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /**
     * @test
     * @dataProvider pathAndParametersAndExpectedPatternProvider
     */
    public function createUrlMatcher_givenPathAndParametersInEndpoint_regularExpressionPatternReturned(
        string $path,
        EndpointParameterCollection $parameters,
        string $expectedPattern
    ): void {
        $factory = $this->createUrlMatcherFactory();
        $pointer = new SpecificationPointer();
        $endpoint = $this->givenEndpoint($path, $parameters);

        /** @var RegularExpressionUrlMatcher $matcher */
        $matcher = $factory->createUrlMatcher($endpoint, $pointer);

        $this->assertInstanceOf(RegularExpressionUrlMatcher::class, $matcher);
        $this->assertSame($expectedPattern, $matcher->getPattern());
        \Phake::verifyNoInteraction($this->errorHandler);
    }

    /** @test */
    public function createUrlMatcher_objectSchemaInParameter_stringPatternReturnedAndErrorReported(): void
    {
        $factory = $this->createUrlMatcherFactory();
        $pointer = new SpecificationPointer(['path']);
        $endpoint = $this->givenEndpoint(
            '/resources/{resourceId}',
            $this->givenParametersWithTypes([
                'resourceId' => new ObjectType(),
            ])
        );

        /** @var RegularExpressionUrlMatcher $matcher */
        $matcher = $factory->createUrlMatcher($endpoint, $pointer);

        $this->assertInstanceOf(RegularExpressionUrlMatcher::class, $matcher);
        $this->assertSame('/^\/resources\/([^\\\\\\/]*)$/', $matcher->getPattern());
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Unsupported schema type for Parameter Object in path, must be one of: "string", "number", "integer".',
            'path'
        );
    }

    /** @test */
    public function createUrlMatcher_pathWithoutParameter_errorReportedAndNullMatcherReturned(): void
    {
        $factory = $this->createUrlMatcherFactory();
        $pointer = new SpecificationPointer(['path']);
        $endpoint = $this->givenEndpoint('/resources/{resourceId}/subresources/{subresourceId}');

        $matcher = $factory->createUrlMatcher($endpoint, $pointer);

        $this->assertInstanceOf(NullUrlMatcher::class, $matcher);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Path has unresolved path segments: {resourceId}, {subresourceId}.',
            'path'
        );
    }

    public function pathAndParametersAndExpectedPatternProvider(): \Iterator
    {
        yield [
            '/resources',
            $this->givenParametersWithTypes(),
            '/^\/resources$/',
        ];
        yield [
            '/resources/{resourceId}',
            $this->givenParametersWithTypes([
                'resourceId' => new StringType(),
            ]),
            '/^\/resources\/([^\\\\\\/]*)$/',
        ];
        yield [
            '/resources/{resourceId}',
            $this->givenParametersWithTypes([
                'resourceId' => new IntegerType(),
            ]),
            '/^\/resources\/(-?\d*)$/',
        ];
        yield [
            '/resources/{resourceId}',
            $this->givenParametersWithTypes([
                'resourceId' => new NumberType(),
            ]),
            '/^\/resources\/(-?(?:\d+|\d*\.\d+))$/',
        ];
        yield [
            '/resources/{resourceId}/subresources/{subresourceId}',
            $this->givenParametersWithTypes([
                'resourceId'    => new StringType(),
                'subresourceId' => new IntegerType(),
            ]),
            '/^\/resources\/([^\\\\\\/]*)\/subresources\/(-?\d*)$/',
        ];
        yield [
            '/resources/{resourceId}',
            $this->givenQueryAndPathParametersWithSameNames(),
            '/^\/resources\/([^\\\\\\/]*)$/',
        ];
    }

    private function givenParametersWithTypes(array $typeByParameterNameMap = []): EndpointParameterCollection
    {
        $parameters = new EndpointParameterCollection();

        foreach ($typeByParameterNameMap as $parameterName => $type) {
            $parameter = new EndpointParameter();
            $parameter->name = $parameterName;
            $parameter->in = new EndpointParameterLocationEnum(EndpointParameterLocationEnum::PATH);
            $parameter->schema = $type;

            $parameters->add($parameter);
        }

        return $parameters;
    }

    private function givenQueryAndPathParametersWithSameNames(): EndpointParameterCollection
    {
        $parameters = new EndpointParameterCollection();

        $pathParameter = new EndpointParameter();
        $pathParameter->name = 'resourceId';
        $pathParameter->in = new EndpointParameterLocationEnum(EndpointParameterLocationEnum::PATH);
        $pathParameter->schema = new StringType();

        $queryParameter = new EndpointParameter();
        $queryParameter->name = 'resourceId';
        $queryParameter->in = new EndpointParameterLocationEnum(EndpointParameterLocationEnum::QUERY);
        $queryParameter->schema = new IntegerType();

        $parameters->add($pathParameter);
        $parameters->add($queryParameter);

        return $parameters;
    }

    private function createUrlMatcherFactory(): UrlMatcherFactory
    {
        return new UrlMatcherFactory($this->errorHandler);
    }

    private function givenEndpoint(string $path, EndpointParameterCollection $parameters = null): Endpoint
    {
        $endpoint = new Endpoint();
        $endpoint->path = $path;
        $endpoint->parameters = $parameters ?? new EndpointParameterCollection();

        return $endpoint;
    }
}
