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

use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Primitive\IntegerTypeParser;
use PHPUnit\Framework\TestCase;

class IntegerTypeParserTest extends TestCase
{
    private const MINIMUM = 10;
    private const MAXIMUM = 100;
    private const MULTIPLE_OF = 2;
    private const NUMBER_SCHEMA = [
        'nullable' => true,
        'minimum' => self::MINIMUM,
        'maximum' => self::MAXIMUM,
        'exclusiveMinimum' => true,
        'exclusiveMaximum' => true,
        'multipleOf' => self::MULTIPLE_OF,
    ];

    /** @test */
    public function parse_integerSchemaWithoutParameters_integerTypeWithDefaultsReturned(): void
    {
        $parser = new IntegerTypeParser();

        /** @var IntegerType $type */
        $type = $parser->parse([], new SpecificationPointer());

        $this->assertInstanceOf(IntegerType::class, $type);
        $this->assertFalse($type->nullable);
        $this->assertNull($type->minimum);
        $this->assertNull($type->maximum);
        $this->assertFalse($type->exclusiveMinimum);
        $this->assertFalse($type->exclusiveMaximum);
        $this->assertNull($type->multipleOf);
    }

    /** @test */
    public function parse_integerSchemaWithParameters_integerTypeWithParametersReturned(): void
    {
        $parser = new IntegerTypeParser();

        /** @var IntegerType $type */
        $type = $parser->parse(self::NUMBER_SCHEMA, new SpecificationPointer());

        $this->assertInstanceOf(IntegerType::class, $type);
        $this->assertTrue($type->nullable);
        $this->assertSame(self::MINIMUM, $type->minimum);
        $this->assertSame(self::MAXIMUM, $type->maximum);
        $this->assertTrue($type->exclusiveMinimum);
        $this->assertTrue($type->exclusiveMaximum);
        $this->assertSame(self::MULTIPLE_OF, $type->multipleOf);
    }
}
