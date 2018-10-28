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

use App\OpenAPI\Parsing\SchemaParser;
use App\Tests\Utility\TestCase\TypeParserTestCaseTrait;
use PHPUnit\Framework\TestCase;

class SchemaParserTest extends TestCase
{
    use TypeParserTestCaseTrait;

    private const VALUE_TYPE = 'value_type';
    private const SCHEMA_DEFINITION = [
        'type' => self::VALUE_TYPE
    ];
    private const VALID_SCHEMA = [
        'schema' => self::SCHEMA_DEFINITION
    ];

    protected function setUp(): void
    {
        $this->setUpTypeParser();
    }

    /** @test */
    public function parseSchema_validSchema_schemaCreatedByTypeParserFromLocator(): void
    {
        $parser = new SchemaParser($this->typeParserLocator);
        $this->givenTypeParserLocator_getTypeParser_returnsTypeParser();
        $type = $this->givenTypeParser_parseTypeSchema_returnsType();

        $parsedSchema = $parser->parseSchema(self::VALID_SCHEMA);

        $this->assertTypeParserLocator_getTypeParser_isCalledOnceWithType(self::VALUE_TYPE);
        $this->assertTypeParser_parseTypeSchema_isCalledOnceWithSchema(self::SCHEMA_DEFINITION);
        $this->assertSame($type, $parsedSchema->value);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid schema
     */
    public function parseSchema_invalidSchema_exceptionThrown(): void
    {
        $parser = new SchemaParser($this->typeParserLocator);

        $parser->parseSchema([]);
    }
}
