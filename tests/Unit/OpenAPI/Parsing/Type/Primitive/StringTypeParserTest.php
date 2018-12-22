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
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Primitive\StringTypeParser;
use App\Tests\Utility\TestCase\LoggerTestCaseTrait;
use PHPUnit\Framework\TestCase;

class StringTypeParserTest extends TestCase
{
    use LoggerTestCaseTrait;

    private const MIN_LENGTH = 5;
    private const MAX_LENGTH = 10;
    private const FORMAT_VALUE = 'formatValue';
    private const PATTERN_VALUE = 'patternValue';
    private const ENUM_VALUE_1 = 'enumValue1';
    private const ENUM_VALUE_2 = 'enumValue2';
    private const VALID_STRING_SCHEMA = [
        'nullable' => true,
        'minLength' => self::MIN_LENGTH,
        'maxLength' => self::MAX_LENGTH,
        'format' => self::FORMAT_VALUE,
        'pattern' => self::PATTERN_VALUE,
        'enum' => [
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
        ]
    ];

    protected function setUp(): void
    {
        $this->setUpLogger();
    }

    /** @test */
    public function parse_stringTypeWithValidParametersSchema_stringTypeWithParametersReturned(): void
    {
        $parser = $this->createStringTypeParser();

        /** @var StringType $type */
        $type = $parser->parsePointedSchema(self::VALID_STRING_SCHEMA, new SpecificationPointer());

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
    public function parse_stringTypeWithoutParametersSchema_stringTypeWithDefaultParametersReturned(): void
    {
        $parser = $this->createStringTypeParser();

        /** @var StringType $type */
        $type = $parser->parsePointedSchema([], new SpecificationPointer());

        $this->assertInstanceOf(StringType::class, $type);
        $this->assertFalse($type->nullable);
        $this->assertSame(self::DEFAULT_LENGTH, $type->minLength);
        $this->assertSame(self::DEFAULT_LENGTH, $type->maxLength);
        $this->assertSame('', $type->format);
        $this->assertSame('', $type->pattern);
        $this->assertCount(0, $type->enum);
    }

    /** @test */
    public function parse_stringTypeWithInvalidEnumSchema_warningLogged(): void
    {
        $parser = $this->createStringTypeParser();

        /** @var StringType $type */
        $type = $parser->parsePointedSchema(self::SCHEMA_WITH_INVALID_ENUM, new SpecificationPointer());

        $this->assertCount(0, $type->enum);
        $this->assertLogger_warning_wasCalledOnce();
    }

    /** @test */
    public function parse_stringTypeWithInvalidLengthsSchema_warningLogged(): void
    {
        $parser = $this->createStringTypeParser();

        /** @var StringType $type */
        $type = $parser->parsePointedSchema(
            [
                'minLength' => -2,
                'maxLength' => -1,
            ],
            new SpecificationPointer()
        );

        $this->assertSame(self::DEFAULT_LENGTH, $type->minLength);
        $this->assertSame(self::DEFAULT_LENGTH, $type->maxLength);
        $this->assertLogger_warning_wasCalledTimes(2);
    }

    /** @test */
    public function parse_stringTypeWithInvalidMaxLengthSchema_warningLogged(): void
    {
        $parser = $this->createStringTypeParser();

        /** @var StringType $type */
        $type = $parser->parsePointedSchema(
            [
                'minLength' => 10,
                'maxLength' => 9,
            ],
            new SpecificationPointer()
        );

        $this->assertSame(10, $type->maxLength);
        $this->assertLogger_warning_wasCalledOnce();
    }

    /** @test */
    public function parse_stringTypeWithInvalidEnumValueSchema_warningLogged(): void
    {
        $parser = $this->createStringTypeParser();

        /** @var StringType $type */
        $type = $parser->parsePointedSchema(self::SCHEMA_WITH_INVALID_ENUM_VALUE, new SpecificationPointer());

        $this->assertCount(1, $type->enum);
        $this->assertContains(self::ENUM_VALUE_1, $type->enum);
        $this->assertLogger_warning_wasCalledOnce();
    }

    private function createStringTypeParser(): StringTypeParser
    {
        return new StringTypeParser($this->logger);
    }
}
