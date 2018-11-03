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
use App\OpenAPI\Parsing\ParsingContext;
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
    private const VALID_SCHEMA = [
        'type' => 'array',
        'items' => self::ITEMS_SCHEMA
    ];
    private const SCHEMA_WITHOUT_ITEMS = [
        'type' => 'array',
    ];

    protected function setUp(): void
    {
        $this->setUpSchemaTransformingParser();
    }

    /** @test */
    public function parse_validSchemaWithItems_itemSchemaParsedByTypeParser(): void
    {
        $parser = $this->createArrayTypeParser();
        $itemsType = $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var ArrayType $type */
        $type = $parser->parse(self::VALID_SCHEMA, new ParsingContext());

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::ITEMS_SCHEMA,
            'items'
        );
        $this->assertSame($itemsType, $type->items);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Section "items" is required
     */
    public function parse_noItemsInSchema_exceptionThrown(): void
    {
        $parser = $this->createArrayTypeParser();

        $parser->parse(self::SCHEMA_WITHOUT_ITEMS, new ParsingContext());
    }

    private function createArrayTypeParser(): ArrayTypeParser
    {
        return new ArrayTypeParser($this->schemaTransformingParser);
    }
}
