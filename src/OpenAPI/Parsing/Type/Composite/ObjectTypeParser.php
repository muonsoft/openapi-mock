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
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\Type\SchemaTransformingParser;
use App\OpenAPI\Parsing\Type\TypeParserInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ObjectTypeParser implements TypeParserInterface
{
    /** @var SchemaTransformingParser */
    private $schemaTransformingParser;

    public function __construct(SchemaTransformingParser $schemaTransformingParser)
    {
        $this->schemaTransformingParser = $schemaTransformingParser;
    }

    public function parse(array $schema, ParsingContext $context): TypeMarkerInterface
    {
        $object = new ObjectType();

        $properties = $schema['properties'] ?? [];
        $propertiesContext = $context->withSubPath('properties');
        foreach ($properties as $propertyName => $propertySchema) {
            $propertyContext = $propertiesContext->withSubPath($propertyName);
            $property = $this->schemaTransformingParser->parse($propertySchema, $propertyContext);
            $object->properties->set($propertyName, $property);
        }

        $requiredContext = $context->withSubPath('required');
        foreach ($schema['required'] as $propertyName) {
            $this->validateProperty($propertyName, $object, $requiredContext);
            $object->required->add($propertyName);
        }

        return $object;
    }

    private function validateProperty($propertyName, ObjectType $object, ParsingContext $context): void
    {
        if (!\is_string($propertyName)) {
            throw new ParsingException('Invalid required property', $context);
        }

        if (!$object->properties->containsKey($propertyName)) {
            throw new ParsingException(
                sprintf('Required property "%s" does not exist', $propertyName),
                $context
            );
        }
    }
}
