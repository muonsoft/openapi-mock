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

use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\SchemaTransformingParser;
use App\Tests\Utility\TestCase\TypeParserTestCaseTrait;
use PHPUnit\Framework\TestCase;

class SchemaTransformingParserTest extends TestCase
{
    use TypeParserTestCaseTrait;

    private const VALUE_TYPE = 'value_type';
    private const SCHEMA_DEFINITION = [
        'type' => self::VALUE_TYPE
    ];

    protected function setUp(): void
    {
        $this->setUpTypeParser();
    }

    /** @test */
    public function parse_schemaWithType_schemaParsedByConcreteTypeParserAndReturned(): void
    {
        $parser = new SchemaTransformingParser($this->typeParserLocator);
        $this->givenTypeParserLocator_getTypeParser_returnsTypeParser();
        $context = new SpecificationPointer();
        $expectedType = $this->givenTypeParser_parse_returnsType();

        $type = $parser->parse(self::SCHEMA_DEFINITION, $context);

        $this->assertTypeParserLocator_getTypeParser_isCalledOnceWithType(self::VALUE_TYPE);
        $this->assertTypeParser_parse_isCalledOnceWithSchemaAndContext(self::SCHEMA_DEFINITION, $context);
        $this->assertSame($expectedType, $type);
    }
}
