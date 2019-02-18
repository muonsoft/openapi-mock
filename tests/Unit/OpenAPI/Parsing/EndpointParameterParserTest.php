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

use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\InvalidObject;
use App\OpenAPI\Parsing\EndpointParameterParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParameterParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const TYPE_SCHEMA = ['typeSchema'];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /**
     * @test
     * @dataProvider validParameterSchemaAndValuesProvider
     */
    public function parsePointedSchema_givenParameterSchema_parameterWithExpectedValuesReturned(
        array $parameterSchema,
        string $name,
        string $in,
        bool $required
    ): void {
        $parser = $this->createEndpointParameterParser();
        $specification = new SpecificationAccessor($parameterSchema);
        $pointer = new SpecificationPointer();
        $this->givenInternalParser_parsePointedSchema_returnsObject();

        /** @var EndpointParameter $parameter */
        $parameter = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInstanceOf(EndpointParameter::class, $parameter);
        $this->assertSame($name, $parameter->name);
        $this->assertSame($in, $parameter->in->getValue());
        $this->assertSame($required, $parameter->required);
    }

    public function validParameterSchemaAndValuesProvider(): \Iterator
    {
        yield [
            [
                'name'     => 'parameterName',
                'in'       => 'path',
                'required' => true,
                'schema'   => [],
            ],
            'parameterName',
            'path',
            true,
        ];
        yield [
            [
                'name'     => 'parameterName',
                'in'       => 'PATH',
                'required' => false,
                'schema'   => [],
            ],
            'parameterName',
            'path',
            true,
        ];
        yield [
            [
                'name'     => 'parameterName',
                'in'       => 'query',
                'required' => false,
                'schema'   => [],
            ],
            'parameterName',
            'query',
            false,
        ];
    }

    /**
     * @test
     * @dataProvider invalidParameterSchemaAndErrorMessageProvider
     */
    public function parsePointedSchema_invalidParameterSchema_invalidObjectReturned(
        $parameterSchema,
        string $errorMessage
    ): void {
        $parser = $this->createEndpointParameterParser();
        $specification = new SpecificationAccessor($parameterSchema);
        $pointer = new SpecificationPointer();

        /** @var InvalidObject $parameter */
        $parameter = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInstanceOf(InvalidObject::class, $parameter);
        $this->assertSame($errorMessage, $parameter->getError());
    }

    public function invalidParameterSchemaAndErrorMessageProvider(): \Iterator
    {
        yield [
            [
                'in' => 'query',
            ],
            'Parameter must have name of string format',
        ];
        yield [
            [
                'name' => 'parameterName',
            ],
            'Parameter location (tag "in") is not present or has invalid type',
        ];
        yield [
            [
                'name' => 'parameterName',
                'in'   => 'invalid',
            ],
            'Invalid parameter location "invalid". Must be one of: query, path, header, cookie.',
        ];
        yield [
            [
                'name' => 'parameterName',
                'in'   => 'query',
            ],
            'Only parameters with "schema" tag are currently supported.',
        ];
        yield [
            [
                'name'   => 'parameterName',
                'in'     => 'query',
                'schema' => 'invalid',
            ],
            'Invalid schema provider for parameter.',
        ];
    }

    /** @test */
    public function parsePointedSchema_parameterSchemaWithTypeSchema_parameterWithSchemaTypeReturned(): void
    {
        $parser = $this->createEndpointParameterParser();
        $specification = new SpecificationAccessor([
            'name'   => 'parameterName',
            'in'     => 'query',
            'schema' => self::TYPE_SCHEMA,
        ]);
        $pointer = new SpecificationPointer();
        $schemaObject = $this->givenInternalParser_parsePointedSchema_returnsObject();

        /** @var EndpointParameter $parameter */
        $parameter = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            ['schema']
        );
        $this->assertInstanceOf(EndpointParameter::class, $parameter);
        $this->assertSame($schemaObject, $parameter->schema);
    }

    private function createEndpointParameterParser(): EndpointParameterParser
    {
        return new EndpointParameterParser($this->internalParser, $this->errorHandler);
    }
}
