<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing;

use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\SchemaParser;
use App\Tests\Utility\TestCase\SchemaTransformingParserTestCase;
use PHPUnit\Framework\TestCase;

class SchemaParserTest extends TestCase
{
    use SchemaTransformingParserTestCase;

    private const VALUE_TYPE = 'value_type';
    private const SCHEMA_DEFINITION = [
        'type' => self::VALUE_TYPE
    ];
    private const VALID_SCHEMA = [
        'schema' => self::SCHEMA_DEFINITION
    ];

    protected function setUp(): void
    {
        $this->setUpSchemaTransformingParser();
    }

    /** @test */
    public function parse_validSchema_schemaCreatedByTypeParserFromLocator(): void
    {
        $parser = $this->createSchemaParser();
        $type = $this->givenSchemaTransformingParser_parse_returnsType();

        $parsedSchema = $parser->parse(self::VALID_SCHEMA, new ParsingContext());

        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::SCHEMA_DEFINITION,
            'schema'
        );
        $this->assertSame($type, $parsedSchema->value);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid schema
     */
    public function parse_invalidSchema_exceptionThrown(): void
    {
        $parser = $this->createSchemaParser();

        $parser->parse([], new ParsingContext());
    }

    private function createSchemaParser(): SchemaParser
    {
        return new SchemaParser($this->schemaTransformingParser);
    }
}
