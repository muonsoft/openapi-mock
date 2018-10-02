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
use App\OpenAPI\Parsing\Type\Composite\ObjectTypeParser;
use App\Tests\Utility\TestCase\TypeParserTestCaseTrait;
use PHPUnit\Framework\TestCase;

class ObjectTypeParserTest extends TestCase
{
    use TypeParserTestCaseTrait;

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

    protected function setUp(): void
    {
        $this->setUpTypeParser();
    }

    /** @test */
    public function parseTypeSchema_validSchemaWithProperties_propertiesParsedByTypeParsers(): void
    {
        $parser = new ObjectTypeParser($this->typeParserLocator);
        $this->givenTypeParserLocator_getTypeParser_returnsTypeParser();
        $expectedPropertyType = $this->givenTypeParser_parseTypeSchema_returnsType();

        /** @var ObjectType $object */
        $object = $parser->parseTypeSchema(self::VALID_OBJECT_SCHEMA);

        $this->assertTypeParserLocator_getTypeParser_isCalledOnceWithType(self::PROPERTY_TYPE);
        $this->assertTypeParser_parseTypeSchema_isCalledOnceWithSchema(self::PROPERTY_SCHEMA);
        $this->assertObjectIsValidAndHasProperty($object, $expectedPropertyType);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessageRegExp /Required property .* does not exist/
     */
    public function parseTypeSchema_requiredPropertyDoesNotExist_exceptionThrown(): void
    {
        $parser = new ObjectTypeParser($this->typeParserLocator);
        $this->givenTypeParserLocator_getTypeParser_returnsTypeParser();
        $this->givenTypeParser_parseTypeSchema_returnsType();

        $parser->parseTypeSchema([
            'type' => 'object',
            'properties' => [
                self::PROPERTY_NAME => self::PROPERTY_SCHEMA
            ],
            'required' => [
                'not_exist',
            ]
        ]);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid required property
     */
    public function parseTypeSchema_invalidRequiredProperty_exceptionThrown(): void
    {
        $parser = new ObjectTypeParser($this->typeParserLocator);
        $this->givenTypeParserLocator_getTypeParser_returnsTypeParser();
        $this->givenTypeParser_parseTypeSchema_returnsType();

        $parser->parseTypeSchema(['required' => [[]]]);
    }

    private function assertObjectIsValidAndHasProperty(ObjectType $object, TypeMarkerInterface $propertyType): void
    {
        $this->assertCount(1, $object->properties);
        $this->assertSame($propertyType, $object->properties->first());
        $this->assertSame([self::PROPERTY_NAME], $object->properties->getKeys());
        $this->assertSame([self::PROPERTY_NAME], $object->required->toArray());
    }
}
