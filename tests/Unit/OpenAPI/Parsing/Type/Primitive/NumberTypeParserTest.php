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

use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\Type\Primitive\NumberTypeParser;
use PHPUnit\Framework\TestCase;

class NumberTypeParserTest extends TestCase
{
    private const MINIMUM = 10.5;
    private const MAXIMUM = 100.5;
    private const MULTIPLE_OF = 2.5;
    private const NUMBER_SCHEMA = [
        'nullable' => true,
        'minimum' => self::MINIMUM,
        'maximum' => self::MAXIMUM,
        'exclusiveMinimum' => true,
        'exclusiveMaximum' => true,
        'multipleOf' => self::MULTIPLE_OF,
    ];

    /** @test */
    public function parse_numberSchemaWithoutParameters_numberTypeWithDefaultsReturned(): void
    {
        $parser = new NumberTypeParser();

        /** @var NumberType $type */
        $type = $parser->parse([], new ParsingContext());

        $this->assertInstanceOf(NumberType::class, $type);
        $this->assertFalse($type->nullable);
        $this->assertNull($type->minimum);
        $this->assertNull($type->maximum);
        $this->assertFalse($type->exclusiveMinimum);
        $this->assertFalse($type->exclusiveMaximum);
        $this->assertNull($type->multipleOf);
    }

    /** @test */
    public function parse_numberSchemaWithParameters_numberTypeWithParametersReturned(): void
    {
        $parser = new NumberTypeParser();

        /** @var NumberType $type */
        $type = $parser->parse(self::NUMBER_SCHEMA, new ParsingContext());

        $this->assertInstanceOf(NumberType::class, $type);
        $this->assertTrue($type->nullable);
        $this->assertSame(self::MINIMUM, $type->minimum);
        $this->assertSame(self::MAXIMUM, $type->maximum);
        $this->assertTrue($type->exclusiveMinimum);
        $this->assertTrue($type->exclusiveMaximum);
        $this->assertSame(self::MULTIPLE_OF, $type->multipleOf);
    }
}
