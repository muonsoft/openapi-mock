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
use App\OpenAPI\Parsing\Type\Composite\ArrayTypeParser;
use App\Tests\Utility\TestCase\TypeParserTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ArrayTypeParserTest extends TestCase
{
    use TypeParserTestCaseTrait;

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
        $this->setUpTypeParser();
    }

    /** @test */
    public function parseTypeSchema_validSchemaWithItems_itemSchemaParsedByTypeParser(): void
    {
        $parser = $this->createArrayTypeParser();
        $this->givenTypeParserLocator_getTypeParser_returnsTypeParser();
        $itemsType = $this->givenTypeParser_parseTypeSchema_returnsType();

        /** @var ArrayType $type */
        $type = $parser->parseTypeSchema(self::VALID_SCHEMA);

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertTypeParserLocator_getTypeParser_isCalledOnceWithType(self::ITEMS_SCHEMA_TYPE);
        $this->assertTypeParser_parseTypeSchema_isCalledOnceWithSchema(self::ITEMS_SCHEMA);
        $this->assertSame($itemsType, $type->items);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Items schema is required
     */
    public function parseTypeSchema_noItemsInSchema_exceptionThrown(): void
    {
        $parser = $this->createArrayTypeParser();

        $parser->parseTypeSchema(self::SCHEMA_WITHOUT_ITEMS);
    }

    private function createArrayTypeParser(): ArrayTypeParser
    {
        return new ArrayTypeParser($this->typeParserLocator);
    }
}
