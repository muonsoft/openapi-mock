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
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\InvalidObject;
use App\OpenAPI\Parsing\EndpointParameterCollectionParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParameterCollectionParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const TYPE_SCHEMA = ['typeSchema'];
    private const PARAMETER_SCHEMA = ['parameterSchema'];
    private const ERROR = 'error';

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_validParameterSchema_parameterReturnedInCollection(): void
    {
        $parser = $this->createEndpointParameterCollectionParser();
        $specification = new SpecificationAccessor([self::PARAMETER_SCHEMA]);
        $pointer = new SpecificationPointer();
        $expectedParameter = new EndpointParameter();
        $this->givenInternalParser_parsePointedSchema_returns($expectedParameter);

        /** @var EndpointParameterCollection $parameters */
        $parameters = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            ['0']
        );
        $this->assertInstanceOf(EndpointParameterCollection::class, $parameters);
        $this->assertCount(1, $parameters);
        $this->assertSame($expectedParameter, $parameters->first());
    }

    /** @test */
    public function parsePointedSchema_invalidParameterSchema_parametersCollectionIsEmptyAndErrorReported(): void
    {
        $parser = $this->createEndpointParameterCollectionParser();
        $specification = new SpecificationAccessor([self::PARAMETER_SCHEMA]);
        $pointer = new SpecificationPointer();
        $expectedParameter = new InvalidObject(self::ERROR);
        $this->givenInternalParser_parsePointedSchema_returns($expectedParameter);

        /** @var EndpointParameterCollection $parameters */
        $parameters = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            ['0']
        );
        $this->assertInstanceOf(EndpointParameterCollection::class, $parameters);
        $this->assertCount(0, $parameters);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            self::ERROR,
            ['0']
        );
    }

    private function createEndpointParameterCollectionParser(): EndpointParameterCollectionParser
    {
        return new EndpointParameterCollectionParser($this->internalParser, $this->errorHandler);
    }
}
