<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Type\Combined;

use App\Mock\Parameters\Schema\Type\Combined\AbstractCombinedType;
use App\Mock\Parameters\Schema\Type\Combined\AllOfType;
use App\Mock\Parameters\Schema\Type\Combined\AnyOfType;
use App\Mock\Parameters\Schema\Type\Combined\OneOfType;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\InvalidType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Combined\CombinedTypeParser;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class CombinedTypeParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const TYPE_SCHEMA = 'typeSchema';

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /**
     * @test
     * @dataProvider combinedTypeNameAndClassProvider
     */
    public function parsePointedSchema_combinedTypeSchema_combinedTypeWithParsedValuesCreatedAndReturned(
        string $combinedTypeName,
        string $combinedTypeClass
    ): void {
        $typeParser = $this->createCombinedTypeParser();
        $specification = new SpecificationAccessor([
            $combinedTypeName => [
                self::TYPE_SCHEMA,
            ],
        ]);
        $internalType = new ObjectType();
        $this->givenContextualParser_parsePointedSchema_returns($internalType);

        /** @var OneOfType $type */
        $type = $typeParser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf($combinedTypeClass, $type);
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            [$combinedTypeName, '0']
        );
        $this->assertCount(1, $type->types);
        $this->assertSame($internalType, $type->types->get(0));
    }

    /**
     * @test
     * @dataProvider combinedTypeNameAndClassProvider
     */
    public function parsePointedSchema_invalidCombinedTypeSchema_errorReported(string $combinedTypeName): void
    {
        $typeParser = $this->createCombinedTypeParser();
        $specification = new SpecificationAccessor([$combinedTypeName => 'invalid']);
        $errorMessage = $this->givenParsingErrorHandler_reportError_returnsMessage();

        /** @var InvalidType $type */
        $type = $typeParser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(InvalidType::class, $type);
        $this->assertSame($errorMessage, $type->getError());
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Value must be not empty array',
            $combinedTypeName
        );
    }

    public function combinedTypeNameAndClassProvider(): array
    {
        return [
            ['oneOf', OneOfType::class],
            ['anyOf', AnyOfType::class],
            ['allOf', AllOfType::class],
        ];
    }

    /** @test */
    public function parsePointedSchema_schemaWithUnknownCombinedType_errorReported(): void
    {
        $typeParser = $this->createCombinedTypeParser();
        $specification = new SpecificationAccessor([]);
        $errorMessage = $this->givenParsingErrorHandler_reportError_returnsMessage();

        /** @var InvalidType $type */
        $type = $typeParser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(InvalidType::class, $type);
        $this->assertSame($errorMessage, $type->getError());
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Not supported combined type, must be one of: "oneOf", "allOf" or "anyOf"',
            ''
        );
    }

    /**
     * @test
     * @dataProvider objectiveCombinedTypeNameAndClassProvider
     */
    public function parsePointedSchema_combinedTypeSchemaWithInternalTypeThatIsNotObject_errorReported(
        string $combinedTypeName,
        string $combinedTypeClass
    ): void {
        $typeParser = $this->createCombinedTypeParser();
        $specification = new SpecificationAccessor([
            $combinedTypeName => [
                self::TYPE_SCHEMA,
            ],
        ]);
        $internalType = \Phake::mock(TypeInterface::class);
        $this->givenContextualParser_parsePointedSchema_returns($internalType);

        /** @var AbstractCombinedType $type */
        $type = $typeParser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf($combinedTypeClass, $type);
        $this->assertCount(0, $type->types);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'All internal types of "anyOf" or "allOf" schema must be objects',
            $combinedTypeName.'.0'
        );
    }

    public function objectiveCombinedTypeNameAndClassProvider(): array
    {
        return [
            ['anyOf', AnyOfType::class],
            ['allOf', AllOfType::class],
        ];
    }

    private function createCombinedTypeParser(): CombinedTypeParser
    {
        return new CombinedTypeParser($this->contextualParser, $this->errorHandler);
    }
}
