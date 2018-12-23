<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Type;

use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\DelegatingSchemaParser;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class DelegatingSchemaParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const VALUE_TYPE = 'value_type';
    private const SCHEMA_DEFINITION = [
        'type' => self::VALUE_TYPE
    ];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_schemaWithType_schemaParsedByConcreteTypeParserAndReturned(): void
    {
        $parser = new DelegatingSchemaParser($this->typeParserLocator);
        $this->givenTypeParserLocator_getTypeParser_returnsContextualParser();
        $pointer = new SpecificationPointer();
        $expectedType = $this->givenContextualParser_parsePointedSchema_returnsObject();
        $specification = new SpecificationAccessor(self::SCHEMA_DEFINITION);

        $type = $parser->parsePointedSchema($specification, $pointer);

        $this->assertTypeParserLocator_getTypeParser_wasCalledOnceWithType(self::VALUE_TYPE);
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointer($specification, $pointer);
        $this->assertSame($expectedType, $type);
    }
}
