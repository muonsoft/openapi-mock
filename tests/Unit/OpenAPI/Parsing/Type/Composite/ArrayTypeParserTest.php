<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Type\Composite;

use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Composite\ArrayTypeParser;
use App\Tests\Utility\TestCase\SchemaTransformingParserTestCase;
use PHPUnit\Framework\TestCase;

class ArrayTypeParserTest extends TestCase
{
    use SchemaTransformingParserTestCase;

    private const ITEMS_SCHEMA_TYPE = 'itemsSchemaType';
    private const ITEMS_SCHEMA = [
        'type' => self::ITEMS_SCHEMA_TYPE
    ];
    private const VALID_SCHEMA_WITH_PARAMETERS = [
        'type' => 'array',
        'items' => self::ITEMS_SCHEMA,
        'minItems' => self::MIN_ITEMS,
        'maxItems' => self::MAX_ITEMS,
        'uniqueItems' => true,
    ];
    private const VALID_SCHEMA_WITHOUT_PARAMETERS = [
        'type' => 'array',
        'items' => self::ITEMS_SCHEMA,
    ];
    private const SCHEMA_WITHOUT_ITEMS = [
        'type' => 'array',
    ];
    private const MIN_ITEMS = 5;
    private const MAX_ITEMS = 10;

    protected function setUp(): void
    {
        $this->setUpSchemaTransformingParser();
    }

    /** @test */
    public function parse_validSchemaWithItemsAndParameters_itemSchemaParsedByTypeParser(): void
    {
        $parser = $this->createArrayTypeParser();
        $itemsType = $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var ArrayType $type */
        $type = $parser->parsePointedSchema(self::VALID_SCHEMA_WITH_PARAMETERS, new SpecificationPointer());

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::ITEMS_SCHEMA,
            'items'
        );
        $this->assertSame($itemsType, $type->items);
        $this->assertSame(self::MIN_ITEMS, $type->minItems);
        $this->assertSame(self::MAX_ITEMS, $type->maxItems);
        $this->assertTrue($type->uniqueItems);
    }

    /** @test */
    public function parse_validSchemaWithItemsAndNoParameters_itemSchemaParsedByTypeParserAndParametersSetToDefaults(): void
    {
        $parser = $this->createArrayTypeParser();
        $itemsType = $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var ArrayType $type */
        $type = $parser->parsePointedSchema(self::VALID_SCHEMA_WITHOUT_PARAMETERS, new SpecificationPointer());

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::ITEMS_SCHEMA,
            'items'
        );
        $this->assertSame($itemsType, $type->items);
        $this->assertSame(0, $type->minItems);
        $this->assertSame(0, $type->maxItems);
        $this->assertFalse($type->uniqueItems);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Section "items" is required
     */
    public function parse_noItemsInSchema_exceptionThrown(): void
    {
        $parser = $this->createArrayTypeParser();

        $parser->parsePointedSchema(self::SCHEMA_WITHOUT_ITEMS, new SpecificationPointer());
    }

    private function createArrayTypeParser(): ArrayTypeParser
    {
        return new ArrayTypeParser($this->schemaTransformingParser);
    }
}
