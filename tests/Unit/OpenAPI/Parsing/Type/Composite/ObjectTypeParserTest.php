<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Type\Composite;

use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\Type\Composite\ObjectTypeParser;
use App\Tests\Utility\TestCase\SchemaTransformingParserTestCase;
use PHPUnit\Framework\TestCase;

class ObjectTypeParserTest extends TestCase
{
    use SchemaTransformingParserTestCase;

    private const PROPERTY_TYPE = 'propertyType';
    private const PROPERTY_NAME = 'propertyName';
    private const PROPERTY_SCHEMA = [
        'type' => self::PROPERTY_TYPE
    ];
    private const VALID_OBJECT_SCHEMA = [
        'type' => 'object',
        'properties' => [
            self::PROPERTY_NAME => self::PROPERTY_SCHEMA
        ],
        'required' => [
            self::PROPERTY_NAME,
        ]
    ];
    private const PROPERTY_CONTEXT_PATH = 'properties.propertyName';

    protected function setUp(): void
    {
        $this->setUpSchemaTransformingParser();
    }

    /** @test */
    public function parse_validSchemaWithProperties_propertiesParsedByTypeParsers(): void
    {
        $parser = $this->createObjectTypeParser();
        $expectedPropertyType = $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var ObjectType $object */
        $object = $parser->parse(self::VALID_OBJECT_SCHEMA, new ParsingContext());

        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::PROPERTY_SCHEMA,
            self::PROPERTY_CONTEXT_PATH
        );
        $this->assertObjectIsValidAndHasProperty($object, $expectedPropertyType);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessageRegExp /Required property .* does not exist/
     */
    public function parse_requiredPropertyDoesNotExist_exceptionThrown(): void
    {
        $parser = $this->createObjectTypeParser();
        $this->givenSchemaTransformingParser_parse_returnsType();

        $parser->parse(
            [
                'type' => 'object',
                'properties' => [
                    self::PROPERTY_NAME => self::PROPERTY_SCHEMA
                ],
                'required' => [
                    'not_exist',
                ]
            ],
            new ParsingContext()
        );
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid required property
     */
    public function parse_invalidRequiredProperty_exceptionThrown(): void
    {
        $parser = $this->createObjectTypeParser();
        $this->givenSchemaTransformingParser_parse_returnsType();

        $parser->parse(['required' => [[]]], new ParsingContext());
    }

    private function createObjectTypeParser(): ObjectTypeParser
    {
        return new ObjectTypeParser($this->schemaTransformingParser);
    }

    private function assertObjectIsValidAndHasProperty(ObjectType $object, TypeMarkerInterface $propertyType): void
    {
        $this->assertCount(1, $object->properties);
        $this->assertSame($propertyType, $object->properties->first());
        $this->assertSame([self::PROPERTY_NAME], $object->properties->getKeys());
        $this->assertSame([self::PROPERTY_NAME], $object->required->toArray());
    }
}
