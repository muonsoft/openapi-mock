<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Type\Primitive;

use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Primitive\StringTypeParser;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class StringTypeParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const MIN_LENGTH = 5;
    private const MAX_LENGTH = 10;
    private const FORMAT_VALUE = 'formatValue';
    private const PATTERN_VALUE = 'patternValue';
    private const ENUM_VALUE_1 = 'enumValue1';
    private const ENUM_VALUE_2 = 'enumValue2';
    private const VALID_STRING_SCHEMA = [
        'nullable'  => true,
        'minLength' => self::MIN_LENGTH,
        'maxLength' => self::MAX_LENGTH,
        'format'    => self::FORMAT_VALUE,
        'pattern'   => self::PATTERN_VALUE,
        'enum'      => [
            self::ENUM_VALUE_1,
            self::ENUM_VALUE_2,
        ],
    ];
    private const DEFAULT_LENGTH = 0;
    private const SCHEMA_WITH_INVALID_ENUM = ['enum' => 'invalid'];
    private const SCHEMA_WITH_INVALID_ENUM_VALUE = [
        'enum' => [
            self::ENUM_VALUE_1,
            ['invalid'],
        ],
    ];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_stringTypeWithValidParametersSchema_stringTypeWithParametersReturned(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor(self::VALID_STRING_SCHEMA);

        /** @var StringType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(StringType::class, $type);
        $this->assertTrue($type->nullable);
        $this->assertSame(self::MIN_LENGTH, $type->minLength);
        $this->assertSame(self::MAX_LENGTH, $type->maxLength);
        $this->assertSame(self::FORMAT_VALUE, $type->format);
        $this->assertSame(self::PATTERN_VALUE, $type->pattern);
        $this->assertCount(2, $type->enum);
        $this->assertContains(self::ENUM_VALUE_1, $type->enum);
        $this->assertContains(self::ENUM_VALUE_2, $type->enum);
    }

    /** @test */
    public function parsePointedSchema_stringTypeWithoutParametersSchema_stringTypeWithDefaultParametersReturned(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor([]);

        /** @var StringType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(StringType::class, $type);
        $this->assertFalse($type->nullable);
        $this->assertSame(self::DEFAULT_LENGTH, $type->minLength);
        $this->assertSame(self::DEFAULT_LENGTH, $type->maxLength);
        $this->assertSame('', $type->format);
        $this->assertSame('', $type->pattern);
        $this->assertCount(0, $type->enum);
    }

    /** @test */
    public function parsePointedSchema_stringTypeWithInvalidEnumSchema_warningReported(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor(self::SCHEMA_WITH_INVALID_ENUM);

        /** @var StringType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(0, $type->enum);
        $this->assertParsingErrorHandler_reportWarning_wasCalledOnceWithMessageAndPointerPath(
            'Invalid enum value ""invalid"". Expected to be array of strings.',
            ''
        );
    }

    /** @test */
    public function parsePointedSchema_stringTypeWithInvalidMinLength_warningReported(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor([
            'minLength' => -2,
        ]);

        /** @var StringType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertSame(self::DEFAULT_LENGTH, $type->minLength);
        $this->assertSame(self::DEFAULT_LENGTH, $type->maxLength);
        $this->assertParsingErrorHandler_reportWarning_wasCalledOnceWithMessageAndPointerPath(
            'Property "minLength" cannot be less than 0. Value is ignored.',
            ''
        );
    }

    /** @test */
    public function parsePointedSchema_stringTypeWithInvalidMaxLength_warningReported(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor([
            'maxLength' => -1,
        ]);

        /** @var StringType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertSame(self::DEFAULT_LENGTH, $type->minLength);
        $this->assertSame(self::DEFAULT_LENGTH, $type->maxLength);
        $this->assertParsingErrorHandler_reportWarning_wasCalledOnceWithMessageAndPointerPath(
            'Property "maxLength" cannot be less than 0. Value is ignored.',
            ''
        );
    }

    /** @test */
    public function parsePointedSchema_stringTypeWithInvalidMaxLengthSchema_warningReported(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor([
            'minLength' => 10,
            'maxLength' => 9,
        ]);

        /** @var StringType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertSame(10, $type->maxLength);
        $this->assertParsingErrorHandler_reportWarning_wasCalledOnceWithMessageAndPointerPath(
            'Property "maxLength" cannot be greater than "minLength". Value is set to "minLength".',
            ''
        );
    }

    /** @test */
    public function parsePointedSchema_stringTypeWithInvalidEnumValueSchema_warningReported(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor(self::SCHEMA_WITH_INVALID_ENUM_VALUE);

        /** @var StringType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(1, $type->enum);
        $this->assertContains(self::ENUM_VALUE_1, $type->enum);
        $this->assertParsingErrorHandler_reportWarning_wasCalledOnceWithMessageAndPointerPath(
            'Invalid enum value "["invalid"]" ignored. Value must be valid string.',
            ''
        );
    }

    /** @test */
    public function parsePointedSchema_fixedFieldsSchema_typeWithValidFixedFieldsReturned(): void
    {
        $parser = $this->createStringTypeParser();
        $specification = new SpecificationAccessor([
            'nullable'  => true,
            'readOnly'  => true,
            'writeOnly' => true,
        ]);

        /** @var TypeInterface $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertTrue($type->isNullable());
        $this->assertTrue($type->isReadOnly());
        $this->assertTrue($type->isWriteOnly());
    }

    private function createStringTypeParser(): StringTypeParser
    {
        return new StringTypeParser($this->errorHandler);
    }
}
