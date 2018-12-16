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

use App\Mock\Parameters\Schema\Type\Composite\FreeFormObjectType;
use App\Mock\Parameters\Schema\Type\Composite\HashMapType;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\TypeCollection;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\Type\SchemaTransformingParser;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\Utility\StringList;

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
        if (array_key_exists('additionalProperties', $schema)) {
            $object = $this->parseFreeFormOrHashMap($schema, $context);
        } else {
            $object = $this->parseObjectType($schema, $context);
        }

        return $object;
    }

    private function parseObjectType(array $schema, ParsingContext $context): ObjectType
    {
        $object = new ObjectType();

        $object->properties = $this->parseProperties($schema, $context);
        $object->required = $this->parseRequiredProperties($schema, $context, $object->properties);

        return $object;
    }

    private function parseFreeFormOrHashMap(array $schema, ParsingContext $context): TypeMarkerInterface
    {
        $additionalProperties = $this->getAdditionalPropertiesFromSchema($schema, $context);

        if (\count($additionalProperties) === 0) {
            $object = new FreeFormObjectType();
        } else {
            $object = $this->parseHashMap($schema, $context);
        }

        $object->minProperties = $this->readIntegerValue($schema, 'minProperties');
        $object->maxProperties = $this->readIntegerValue($schema, 'maxProperties');

        return $object;
    }

    private function getAdditionalPropertiesFromSchema(array $schema, ParsingContext $context): array
    {
        if ($schema['additionalProperties'] === true) {
            $additionalProperties = [];
        } elseif (\is_array($schema['additionalProperties'])) {
            $additionalProperties = $schema['additionalProperties'];
        } else {
            throw new ParsingException('Invalid value of option "additionalProperties"', $context);
        }

        return $additionalProperties;
    }

    private function parseHashMap(array $schema, ParsingContext $context): HashMapType
    {
        $object = new HashMapType();

        $propertyContext = $context->withSubPath('additionalProperties');
        $object->value = $this->schemaTransformingParser->parse($schema['additionalProperties'], $propertyContext);

        $object->properties = $this->parseProperties($schema, $context);
        $object->required = $this->parseRequiredProperties($schema, $context, $object->properties);

        return $object;
    }

    private function parseProperties(array $schema, ParsingContext $context): TypeCollection
    {
        $properties = new TypeCollection();

        $schemaProperties = $schema['properties'] ?? [];
        $propertiesContext = $context->withSubPath('properties');

        foreach ($schemaProperties as $propertyName => $propertySchema) {
            $propertyContext = $propertiesContext->withSubPath($propertyName);
            $property = $this->schemaTransformingParser->parse($propertySchema, $propertyContext);
            $properties->set($propertyName, $property);
        }

        return $properties;
    }

    private function parseRequiredProperties(array $schema, ParsingContext $context, TypeCollection $properties): StringList
    {
        $requiredProperties = new StringList();

        $schemaRequiredProperties = $schema['required'] ?? [];
        $requiredContext = $context->withSubPath('required');

        foreach ($schemaRequiredProperties as $propertyName) {
            $this->validateProperty($propertyName, $properties, $requiredContext);
            $requiredProperties->add($propertyName);
        }

        return $requiredProperties;
    }

    private function validateProperty($propertyName, TypeCollection $properties, ParsingContext $context): void
    {
        if (!\is_string($propertyName)) {
            throw new ParsingException('Invalid required property', $context);
        }

        if (!$properties->containsKey($propertyName)) {
            throw new ParsingException(
                sprintf('Required property "%s" does not exist', $propertyName),
                $context
            );
        }
    }

    private function readIntegerValue(array $schema, string $key): int
    {
        return (int) ($schema[$key] ?? 0);
    }
}
