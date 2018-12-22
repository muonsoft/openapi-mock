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
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ContextualParserTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ResponseParserTest extends TestCase
{
    use ContextualParserTestCaseTrait;

    private const SCHEMA = ['schema'];
    private const MEDIA_TYPE = 'application/json';
    private const VALID_RESPONSE_SPECIFICATION = [
        'content' => [
            self::MEDIA_TYPE => self::SCHEMA,
        ]
    ];
    private const CONTEXT_PATH = 'content.' . self::MEDIA_TYPE;

    protected function setUp(): void
    {
        $this->setUpContextualParser();
    }

    /** @test */
    public function parse_validResponseSpecification_mockResponseCreatedAndReturned(): void
    {
        $parser = $this->createResponseParser();
        $expectedSchema = new Schema();
        $this->givenContextualParser_parse_returns($expectedSchema);

        $response = $parser->parse(self::VALID_RESPONSE_SPECIFICATION, new SpecificationPointer());

        $this->assertContextualParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::SCHEMA,
            self::CONTEXT_PATH
        );
        $this->assertResponseHasValidContentWithExpectedSchema($response, $expectedSchema);
    }

    /** @test */
    public function parse_noContentInResponseSpecification_emptyContentInMockResponse(): void
    {
        $parser = $this->createResponseParser();

        $response = $parser->parse([], new SpecificationPointer());

        $this->assertCount(0, $response->content);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid response content
     */
    public function parse_invalidContentInResponseSpecification_exceptionThrown(): void
    {
        $parser = $this->createResponseParser();

        $parser->parse(['content' => 'invalid'], new SpecificationPointer());
    }

    private function assertResponseHasValidContentWithExpectedSchema(MockResponse $response, Schema $expectedSchema): void
    {
        $this->assertCount(1, $response->content);
        $parsedSchema = $response->content->first();
        $this->assertSame($expectedSchema, $parsedSchema);
        $this->assertEquals([self::MEDIA_TYPE], $response->content->getKeys());
    }

    private function createResponseParser(): ResponseParser
    {
        return new ResponseParser($this->contextualParser);
    }
}
