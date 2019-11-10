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
use App\OpenAPI\Parsing\MediaParser;
use App\OpenAPI\Parsing\ResponseParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\Assert;
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

    /** @var MediaParser */
    private $mediaParser;

    protected function setUp(): void
    {
        $this->mediaParser = \Phake::mock(MediaParser::class);
    }

    /** @test */
    public function parsePointedSchema_validResponseSpecification_mockResponseCreatedAndReturned(): void
    {
        $parser = $this->createResponseParser();
        $expectedSchema = new Schema();
        $this->givenMediaParser_parseMediaScheme_returns($expectedSchema);
        $specification = new SpecificationAccessor(self::VALID_RESPONSE_SPECIFICATION);

        /** @var MockResponse $response */
        $response = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertMediaParser_parseMediaScheme_wasCalledOnceWithSpecificationAndPointerPathAndMediaType(
            $specification,
            self::CONTEXT_PATH,
            self::MEDIA_TYPE
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
    public function parsePointedSchema_invalidContentInResponseSpecification_errorReported(): void
    {
        $parser = $this->createResponseParser();
        $specification = new SpecificationAccessor(['content' => 'invalid']);

        /** @var MockResponse $response */
        $response = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertCount(0, $response->content);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Invalid response content',
            ['content']
        );
    }

    private function assertResponseHasValidContentWithExpectedSchema(MockResponse $response, Schema $expectedSchema): void
    {
        $this->assertCount(1, $response->content);
        $parsedSchema = $response->content->get(self::MEDIA_TYPE);
        $this->assertSame($expectedSchema, $parsedSchema);
        $this->assertSame([self::MEDIA_TYPE], $response->content->keys());
    }

    private function assertMediaParser_parseMediaScheme_wasCalledOnceWithSpecificationAndPointerPathAndMediaType(
        SpecificationAccessor $specification,
        array $path,
        string $mediaType
    ): void {
        /* @var SpecificationPointer $pointer */
        \Phake::verify($this->mediaParser)
            ->parseMediaScheme($specification, \Phake::capture($pointer), $mediaType);
        Assert::assertSame($path, $pointer->getPathElements());
    }

    private function givenMediaParser_parseMediaScheme_returns(SpecificationObjectMarkerInterface ...$objects): void
    {
        $parser = \Phake::when($this->mediaParser)->parseMediaScheme(\Phake::anyParameters());

        foreach ($objects as $object) {
            $parser = $parser->thenReturn($object);
        }
    }

    private function createResponseParser(): ResponseParser
    {
        return new ResponseParser($this->mediaParser, $this->errorHandler, new NullLogger());
    }
}
