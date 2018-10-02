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

use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\SchemaParser;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\Parsing\TypeParserLocator;
use PHPUnit\Framework\TestCase;

class SchemaParserTest extends TestCase
{
    private const VALUE_TYPE = 'value_type';
    private const VALID_SCHEMA = [
        'schema' => [
            'type' => self::VALUE_TYPE
        ]
    ];

    /** @var TypeParserLocator */
    private $typeParserLocator;

    /** @var TypeParserInterface */
    private $typeParser;

    protected function setUp(): void
    {
        $this->typeParserLocator = \Phake::mock(TypeParserLocator::class);
        $this->typeParser = \Phake::mock(TypeParserInterface::class);
    }

    /** @test */
    public function parseSchema_validSchema_schemaCreatedByTypeParserFromLocator(): void
    {
        $parser = new SchemaParser($this->typeParserLocator);
        $this->givenTypeParserLocator_getTypeParser_returnsTypeParser();
        $type = $this->givenTypeParser_parseTypeSchema_returnsType();

        $parsedSchema = $parser->parseSchema(self::VALID_SCHEMA);

        $this->assertTypeParserLocator_getTypeParser_isCalledOnceWithType(self::VALUE_TYPE);
        $this->assertTypeParser_parseTypeSchema_isCalledOnceWithSchema(self::VALID_SCHEMA);
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

    private function assertTypeParserLocator_getTypeParser_isCalledOnceWithType(string $type): void
    {
        \Phake::verify($this->typeParserLocator)
            ->getTypeParser($type);
    }

    private function assertTypeParser_parseTypeSchema_isCalledOnceWithSchema(array $schema): void
    {
        \Phake::verify($this->typeParser)
            ->parseTypeSchema($schema);
    }

    private function givenTypeParserLocator_getTypeParser_returnsTypeParser(): void
    {
        \Phake::when($this->typeParserLocator)
            ->getTypeParser(\Phake::anyParameters())
            ->thenReturn($this->typeParser);
    }

    private function givenTypeParser_parseTypeSchema_returnsType(): TypeMarkerInterface
    {
        $type = \Phake::mock(TypeMarkerInterface::class);

        \Phake::when($this->typeParser)
            ->parseTypeSchema(\Phake::anyParameters())
            ->thenReturn($type);

        return $type;
    }
}
