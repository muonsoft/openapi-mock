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

use App\Mock\Parameters\Schema\Type\Composite\FreeFormObjectType;
use App\Mock\Parameters\Schema\Type\Composite\HashMapType;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\SpecificationPointer;
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
    private const DEFAULT_PROPERTY_NAME = 'defaultPropertyName';
    private const DEFAULT_PROPERTY_SCHEMA = [
        'type' => 'defaultPropertyType'
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
    private const DEFAULT_PROPERTY_CONTEXT_PATH = 'properties.defaultPropertyName';
    private const HASH_MAP_SCHEMA = [
        'type' => 'object',
        'additionalProperties' => self::PROPERTY_SCHEMA
    ];
    private const HASH_MAP_SCHEMA_WITH_DEFAULT_PROPERTIES = [
        'type' => 'object',
        'additionalProperties' => self::PROPERTY_SCHEMA,
        'properties' => [
            self::DEFAULT_PROPERTY_NAME => self::DEFAULT_PROPERTY_SCHEMA,
        ],
        'required' => [
            self::DEFAULT_PROPERTY_NAME,
        ],
    ];
    private const HASH_MAP_SCHEMA_WITH_MIN_MAX = [
        'type' => 'object',
        'additionalProperties' => self::PROPERTY_SCHEMA,
        'minProperties' => self::MIN_PROPERTIES,
        'maxProperties' => self::MAX_PROPERTIES,
    ];
    private const FREE_FORM_SCHEMA = [
        'type' => 'object',
        'additionalProperties' => true,
    ];
    private const FREE_FORM_SCHEMA_WITH_MIN_MAX = [
        'type' => 'object',
        'additionalProperties' => true,
        'minProperties' => self::MIN_PROPERTIES,
        'maxProperties' => self::MAX_PROPERTIES,
    ];
    private const MIN_PROPERTIES = 1;
    private const MAX_PROPERTIES = 2;

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
        $object = $parser->parsePointedSchema(self::VALID_OBJECT_SCHEMA, new SpecificationPointer());

        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::PROPERTY_SCHEMA,
            self::PROPERTY_CONTEXT_PATH
        );
        $this->assertObjectIsValidAndHasProperty($object, $expectedPropertyType);
    }

    /**
     * @test
     * @dataProvider freeFormAdditionalPropertiesProvider
     */
    public function parse_validSchemaWithFreeFormAdditionalProperties_freeFormObjectTypeReturned(
        $additionalProperties
    ): void {
        $parser = $this->createObjectTypeParser();
        $schema = [
            'type' => 'object',
            'additionalProperties' => $additionalProperties
        ];

        $object = $parser->parsePointedSchema($schema, new SpecificationPointer());

        $this->assertInstanceOf(FreeFormObjectType::class, $object);
    }

    public function freeFormAdditionalPropertiesProvider(): array
    {
        return [
            [true],
            [[]],
        ];
    }

    /** @test */
    public function parse_schemaWithFreeFormAndEmptyMinMaxValues_freeFormObjectWithDefaultMinMax(): void
    {
        $parser = $this->createObjectTypeParser();

        /** @var FreeFormObjectType $object */
        $object = $parser->parsePointedSchema(self::FREE_FORM_SCHEMA, new SpecificationPointer());

        $this->assertInstanceOf(FreeFormObjectType::class, $object);
        $this->assertSame(0, $object->minProperties);
        $this->assertSame(0, $object->maxProperties);
    }

    /** @test */
    public function parse_schemaWithFreeFormAndGivenMinMaxValues_freeFormObjectWithExpectedMinMax(): void
    {
        $parser = $this->createObjectTypeParser();

        /** @var FreeFormObjectType $object */
        $object = $parser->parsePointedSchema(self::FREE_FORM_SCHEMA_WITH_MIN_MAX, new SpecificationPointer());

        $this->assertInstanceOf(FreeFormObjectType::class, $object);
        $this->assertSame(self::MIN_PROPERTIES, $object->minProperties);
        $this->assertSame(self::MAX_PROPERTIES, $object->maxProperties);
    }

    /** @test */
    public function parse_validSchemaWithHashMapAdditionalProperties_hashMapTypeReturned(): void
    {
        $parser = $this->createObjectTypeParser();
        $type = $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var HashMapType $object */
        $object = $parser->parsePointedSchema(self::HASH_MAP_SCHEMA, new SpecificationPointer());

        $this->assertInstanceOf(HashMapType::class, $object);
        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::PROPERTY_SCHEMA,
            'additionalProperties'
        );
        $this->assertSame($type, $object->value);
    }

    /** @test */
    public function parse_hashMapSchemaWithEmptyMinMaxValues_hashMapWithDefaultMinMax(): void
    {
        $parser = $this->createObjectTypeParser();
        $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var HashMapType $object */
        $object = $parser->parsePointedSchema(self::HASH_MAP_SCHEMA, new SpecificationPointer());

        $this->assertInstanceOf(HashMapType::class, $object);
        $this->assertSame(0, $object->minProperties);
        $this->assertSame(0, $object->maxProperties);
    }

    /** @test */
    public function parse_hashMapSchemaWithGivenMinMaxValues_hashMapWithExpectedMinMax(): void
    {
        $parser = $this->createObjectTypeParser();
        $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var HashMapType $object */
        $object = $parser->parsePointedSchema(self::HASH_MAP_SCHEMA_WITH_MIN_MAX, new SpecificationPointer());

        $this->assertInstanceOf(HashMapType::class, $object);
        $this->assertSame(self::MIN_PROPERTIES, $object->minProperties);
        $this->assertSame(self::MAX_PROPERTIES, $object->maxProperties);
    }

    /** @test */
    public function parse_validSchemaWithDefaultProperties_hashMapTypeWithDefaultPropertiesReturned(): void
    {
        $parser = $this->createObjectTypeParser();
        $type = $this->givenSchemaTransformingParser_parse_returnsType();

        /** @var HashMapType $object */
        $object = $parser->parsePointedSchema(self::HASH_MAP_SCHEMA_WITH_DEFAULT_PROPERTIES, new SpecificationPointer());

        $this->assertInstanceOf(HashMapType::class, $object);
        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::PROPERTY_SCHEMA,
            'additionalProperties'
        );
        $this->assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
            self::DEFAULT_PROPERTY_SCHEMA,
            self::DEFAULT_PROPERTY_CONTEXT_PATH
        );
        $this->assertSame($type, $object->value);
        $this->assertHashMapHasValidDefaultProperty($object, $type);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid value of option "additionalProperties"
     */
    public function parse_invalidSchemaWithFreeFormAdditionalProperties_exceptionThrown(): void {
        $parser = $this->createObjectTypeParser();
        $schema = [
            'type' => 'object',
            'additionalProperties' => 'invalid'
        ];

        $parser->parsePointedSchema($schema, new SpecificationPointer());
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

        $parser->parsePointedSchema(
            [
                'type' => 'object',
                'properties' => [
                    self::PROPERTY_NAME => self::PROPERTY_SCHEMA
                ],
                'required' => [
                    'not_exist',
                ]
            ],
            new SpecificationPointer()
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

        $parser->parsePointedSchema(['required' => [[]]], new SpecificationPointer());
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

    private function assertHashMapHasValidDefaultProperty(HashMapType $type, TypeMarkerInterface $propertyType): void
    {
        $this->assertCount(1, $type->properties);
        $this->assertSame($propertyType, $type->properties->first());
        $this->assertSame([self::DEFAULT_PROPERTY_NAME], $type->properties->getKeys());
        $this->assertSame([self::DEFAULT_PROPERTY_NAME], $type->required->toArray());
    }
}
