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
use App\OpenAPI\Parsing\Type\ReferenceResolvingSchemaParser;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ReferenceResolvingSchemaParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parsePointedSchema_specificationAndPointer_schemaResolvedAndParsedByDelegatingParser(): void
    {
        $resolvingSchemaParser = new ReferenceResolvingSchemaParser($this->internalParser, $this->resolvingParser);
        $specification = new SpecificationAccessor([]);
        $pointer = new SpecificationPointer();
        $expectedObject = \Phake::mock(SpecificationObjectMarkerInterface::class);
        $this->givenReferenceResolvingParser_resolveReferenceAndParsePointedSchema_returns($expectedObject);

        $object = $resolvingSchemaParser->parsePointedSchema($specification, $pointer);

        $this->assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerAndInternalParser(
            $specification,
            $pointer
        );
        $this->assertSame($expectedObject, $object);
    }
}
