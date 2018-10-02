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

use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\Schema\Schema;
use App\OpenAPI\Parsing\ResponseParser;
use App\OpenAPI\Parsing\SchemaParser;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase
{
    private const SCHEMA = ['schema'];
    private const MEDIA_TYPE = 'application/json';
    private const VALID_RESPONSE_SPECIFICATION = [
        'content' => [
            self::MEDIA_TYPE => self::SCHEMA,
        ]
    ];

    /** @var SchemaParser */
    private $schemaParser;

    protected function setUp(): void
    {
        $this->schemaParser = \Phake::mock(SchemaParser::class);
    }

    /** @test */
    public function parseResponse_validResponseSpecification_mockResponseCreatedAndReturned(): void
    {
        $parser = new ResponseParser($this->schemaParser);
        $expectedSchema = $this->givenSchemaParser_parseSchema_returnsSchema();

        $response = $parser->parseResponse(self::VALID_RESPONSE_SPECIFICATION);

        $this->assertSchemaParser_parseSchema_isCalledOnceWithRawSchema(self::SCHEMA);
        $this->assertResponseHasValidContentWithExpectedSchema($response, $expectedSchema);
    }

    /** @test */
    public function parseResponse_noContentInResponseSpecification_emptyContentInMockResponse(): void
    {
        $parser = new ResponseParser($this->schemaParser);

        $response = $parser->parseResponse([]);

        $this->assertCount(0, $response->content);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid response content
     */
    public function parseResponse_invalidContentInResponseSpecification_exceptionThrown(): void
    {
        $parser = new ResponseParser($this->schemaParser);

        $parser->parseResponse([
            'content' => 'invalid'
        ]);
    }

    private function assertSchemaParser_parseSchema_isCalledOnceWithRawSchema($schema): void
    {
        \Phake::verify($this->schemaParser)
            ->parseSchema($schema);
    }

    private function givenSchemaParser_parseSchema_returnsSchema(): Schema
    {
        $schema = new Schema();

        \Phake::when($this->schemaParser)
            ->parseSchema(\Phake::anyParameters())
            ->thenReturn($schema);

        return $schema;
    }

    private function assertResponseHasValidContentWithExpectedSchema(MockResponse $response, Schema $expectedSchema): void
    {
        $this->assertCount(1, $response->content);
        $parsedSchema = $response->content->first();
        $this->assertSame($expectedSchema, $parsedSchema);
        $this->assertEquals([self::MEDIA_TYPE], $response->content->getKeys());
    }
}
