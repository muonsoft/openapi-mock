<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Type\Combined;

use App\Mock\Parameters\Schema\Type\Combined\AllOfType;
use App\Mock\Parameters\Schema\Type\Combined\AnyOfType;
use App\Mock\Parameters\Schema\Type\Combined\OneOfType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Combined\CombinedTypeParser;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class CombinedTypeParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const TYPE_SCHEMA = 'typeSchema';

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /**
     * @test
     * @dataProvider combinedTypeNameAndClassProvider
     */
    public function parsePointedSchema_combinedTypeSchema_combinedTypeWithParsedValuesCreatedAndReturned(
        string $combinedTypeName,
        string $combinedTypeClass
    ): void {
        $typeParser = new CombinedTypeParser($this->contextualParser);
        $specification = new SpecificationAccessor([
            $combinedTypeName => [
                self::TYPE_SCHEMA
            ]
        ]);
        $internalType = \Phake::mock(TypeMarkerInterface::class);
        $this->givenContextualParser_parsePointedSchema_returns($internalType);

        /** @var OneOfType $type */
        $type = $typeParser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf($combinedTypeClass, $type);
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            [$combinedTypeName, '0']
        );
        $this->assertCount(1, $type->types);
        $this->assertSame($internalType, $type->types->get(0));
    }

    /**
     * @test
     * @dataProvider combinedTypeNameAndClassProvider
     */
    public function parsePointedSchema_invalidCombinedTypeSchema_exceptionThrown(string $combinedTypeName): void
    {
        $typeParser = new CombinedTypeParser($this->contextualParser);
        $specification = new SpecificationAccessor([$combinedTypeName => 'invalid']);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Value must be not empty array');

        $typeParser->parsePointedSchema($specification, new SpecificationPointer());
    }

    public function combinedTypeNameAndClassProvider(): array
    {
        return [
            ['oneOf', OneOfType::class],
            ['anyOf', AnyOfType::class],
            ['allOf', AllOfType::class],
        ];
    }

    /** @test */
    public function parsePointedSchema_schemaWithUnknownCombinedType_exceptionThrown(): void
    {
        $typeParser = new CombinedTypeParser($this->contextualParser);
        $specification = new SpecificationAccessor([]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Not supported combined type, must be one of: "oneOf", "allOf" or "anyOf"');

        $typeParser->parsePointedSchema($specification, new SpecificationPointer());
    }
}
