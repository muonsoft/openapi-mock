<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type\Composite;

use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\Parsing\TypeParserLocator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ObjectTypeParser implements TypeParserInterface
{
    /** @var TypeParserLocator */
    private $typeParserLocator;

    public function __construct(TypeParserLocator $typeParserLocator)
    {
        $this->typeParserLocator = $typeParserLocator;
    }

    public function parseTypeSchema(array $schema): TypeMarkerInterface
    {
        $object = new ObjectType();

        $properties = $schema['properties'] ?? [];
        foreach ($properties as $propertyName => $propertySchema) {
            $typeParser = $this->typeParserLocator->getTypeParser($propertySchema['type']);
            $property = $typeParser->parseTypeSchema($propertySchema);
            $object->properties->set($propertyName, $property);
        }

        foreach ($schema['required'] as $propertyName) {
            $this->validateProperty($propertyName, $object);
            $object->required->add($propertyName);
        }

        return $object;
    }

    private function validateProperty($propertyName, ObjectType $object): void
    {
        if (!\is_string($propertyName)) {
            throw new ParsingException('Invalid required property');
        }
        if (!$object->properties->containsKey($propertyName)) {
            throw new ParsingException(sprintf('Required property "%s" does not exist', $propertyName));
        }
    }
}
