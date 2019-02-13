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
use App\Mock\Parameters\Schema\Type\InvalidType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Composite\ArrayTypeParser;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ArrayTypeParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const ITEMS_SCHEMA_TYPE = 'itemsSchemaType';
    private const ITEMS_SCHEMA = [
        'type' => self::ITEMS_SCHEMA_TYPE,
    ];
    private const VALID_SCHEMA_WITH_PARAMETERS = [
        'type'        => 'array',
        'items'       => self::ITEMS_SCHEMA,
        'minItems'    => self::MIN_ITEMS,
        'maxItems'    => self::MAX_ITEMS,
        'uniqueItems' => true,
    ];
    private const VALID_SCHEMA_WITHOUT_PARAMETERS = [
        'type'  => 'array',
        'items' => self::ITEMS_SCHEMA,
    ];
    private const SCHEMA_WITHOUT_ITEMS = [
        'type' => 'array',
    ];
    private const MIN_ITEMS = 5;
    private const MAX_ITEMS = 10;

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_validSchemaWithItemsAndParameters_itemSchemaParsedByTypeParser(): void
    {
        $parser = $this->createArrayTypeParser();
        $itemsType = $this->givenContextualParser_parsePointedSchema_returnsObject();
        $specification = new SpecificationAccessor(self::VALID_SCHEMA_WITH_PARAMETERS);

        /** @var ArrayType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath($specification, ['items']);
        $this->assertSame($itemsType, $type->items);
        $this->assertSame(self::MIN_ITEMS, $type->minItems);
        $this->assertSame(self::MAX_ITEMS, $type->maxItems);
        $this->assertTrue($type->uniqueItems);
    }

    /** @test */
    public function parsePointedSchema_validSchemaWithItemsAndNoParameters_itemSchemaParsedByTypeParserAndParametersSetToDefaults(): void
    {
        $parser = $this->createArrayTypeParser();
        $itemsType = $this->givenContextualParser_parsePointedSchema_returnsObject();
        $specification = new SpecificationAccessor(self::VALID_SCHEMA_WITHOUT_PARAMETERS);

        /** @var ArrayType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(ArrayType::class, $type);
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath($specification, ['items']);
        $this->assertSame($itemsType, $type->items);
        $this->assertSame(0, $type->minItems);
        $this->assertSame(0, $type->maxItems);
        $this->assertFalse($type->uniqueItems);
    }

    /** @test */
    public function parsePointedSchema_noItemsInSchema_errorReported(): void
    {
        $parser = $this->createArrayTypeParser();
        $specification = new SpecificationAccessor(self::SCHEMA_WITHOUT_ITEMS);
        $errorMessage = $this->givenParsingErrorHandler_reportError_returnsMessage();

        /** @var InvalidType $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(InvalidType::class, $type);
        $this->assertSame($errorMessage, $type->getError());
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Section "items" is required',
            ''
        );
    }

    /** @test */
    public function parsePointedSchema_fixedFieldsSchema_typeWithValidFixedFieldsReturned(): void
    {
        $parser = $this->createArrayTypeParser();
        $this->givenContextualParser_parsePointedSchema_returnsObject();
        $specification = new SpecificationAccessor([
            'items'     => self::ITEMS_SCHEMA,
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

    private function createArrayTypeParser(): ArrayTypeParser
    {
        return new ArrayTypeParser($this->contextualParser, $this->errorHandler);
    }
}
