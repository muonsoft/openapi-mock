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

use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\OpenAPI\Parsing\MediaParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MediaParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    /** @test */
    public function parseMediaScheme_usualScheme_schemeParsedAndReturnedWithoutChanges(): void
    {
        $parser = new MediaParser($this->internalParser, new NullLogger());
        $specification = new SpecificationAccessor([]);
        $pointer = new SpecificationPointer();
        $expectedSchema = $this->givenInternalParser_parsePointedSchema_returnsObject();

        $schema = $parser->parseMediaScheme($specification, $pointer, '');

        $this->assertSame($expectedSchema, $schema);
        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointer($specification, $pointer);
    }

    /** @test */
    public function parseMediaScheme_htmlSchemeAndStringSchema_htmlFormatIsSet(): void
    {
        $parser = new MediaParser($this->internalParser, new NullLogger());
        $specification = new SpecificationAccessor([]);
        $pointer = new SpecificationPointer();
        $expectedSchema = new Schema();
        $expectedSchema->value = new StringType();
        $this->givenInternalParser_parsePointedSchema_returns($expectedSchema);

        /** @var Schema $schema */
        $schema = $parser->parseMediaScheme($specification, $pointer, 'text/html');

        $this->assertSame($expectedSchema, $schema);
        $this->assertSame('html', $schema->value->format);
        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointer($specification, $pointer);
    }

    /** @test */
    public function parseMediaScheme_htmlSchemeAndObjectSchema_stringSchemaWithHtmlFormatIsSet(): void
    {
        $parser = new MediaParser($this->internalParser, new NullLogger());
        $specification = new SpecificationAccessor([]);
        $pointer = new SpecificationPointer();
        $expectedSchema = new Schema();
        $expectedSchema->value = new ObjectType();
        $this->givenInternalParser_parsePointedSchema_returns($expectedSchema);

        /** @var Schema $schema */
        $schema = $parser->parseMediaScheme($specification, $pointer, 'text/html');

        $this->assertInstanceOf(StringType::class, $schema->value);
        $this->assertSame('html', $schema->value->format);
        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointer($specification, $pointer);
    }
}
