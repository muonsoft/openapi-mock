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
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\ResponseParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ResponseParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const SCHEMA = ['schema'];
    private const MEDIA_TYPE = 'application/json';
    private const VALID_RESPONSE_SPECIFICATION = [
        'content' => [
            self::MEDIA_TYPE => self::SCHEMA,
        ],
    ];
    private const CONTEXT_PATH = ['content', self::MEDIA_TYPE];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_validResponseSpecification_mockResponseCreatedAndReturned(): void
    {
        $parser = $this->createResponseParser();
        $expectedSchema = new Schema();
        $this->givenContextualParser_parsePointedSchema_returns($expectedSchema);
        $specification = new SpecificationAccessor(self::VALID_RESPONSE_SPECIFICATION);

        /** @var MockResponse $response */
        $response = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            self::CONTEXT_PATH
        );
        $this->assertResponseHasValidContentWithExpectedSchema($response, $expectedSchema);
    }

    /** @test */
    public function parsePointedSchema_noContentInResponseSpecification_emptyContentInMockResponse(): void
    {
        $parser = $this->createResponseParser();
        $specification = new SpecificationAccessor([]);

        /** @var MockResponse $response */
        $response = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(0, $response->content);
    }

    /** @test */
    public function parsePointedSchema_invalidContentInResponseSpecification_exceptionThrown(): void
    {
        $parser = $this->createResponseParser();
        $specification = new SpecificationAccessor(['content' => 'invalid']);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Invalid response content');

        $parser->parsePointedSchema($specification, new SpecificationPointer());
    }

    private function assertResponseHasValidContentWithExpectedSchema(MockResponse $response, Schema $expectedSchema): void
    {
        $this->assertCount(1, $response->content);
        $parsedSchema = $response->content->first();
        $this->assertSame($expectedSchema, $parsedSchema);
        $this->assertSame([self::MEDIA_TYPE], $response->content->getKeys());
    }

    private function createResponseParser(): ResponseParser
    {
        return new ResponseParser($this->contextualParser, new NullLogger());
    }
}
