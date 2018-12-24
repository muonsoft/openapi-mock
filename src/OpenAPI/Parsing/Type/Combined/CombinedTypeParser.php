<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type\Combined;

use App\Mock\Parameters\Schema\Type\Combined\AbstractCombinedType;
use App\Mock\Parameters\Schema\Type\Combined\AllOfType;
use App\Mock\Parameters\Schema\Type\Combined\AnyOfType;
use App\Mock\Parameters\Schema\Type\Combined\OneOfType;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\OpenAPI\Parsing\ContextualParserInterface;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class CombinedTypeParser implements TypeParserInterface
{
    private const COMBINED_TYPES = [
        'oneOf' => OneOfType::class,
        'anyOf' => AnyOfType::class,
        'allOf' => AllOfType::class,
    ];

    /** @var ContextualParserInterface */
    private $resolvingSchemaParser;

    public function __construct(ContextualParserInterface $resolvingSchemaParser)
    {
        $this->resolvingSchemaParser = $resolvingSchemaParser;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $schema = $specification->getSchema($pointer);

        $typeName = $this->getSchemaTypeName($schema, $pointer);
        $type = $this->createCombinedType($typeName);

        $typePointer = $pointer->withPathElement($typeName);
        $this->validateSchema($schema, $typeName, $typePointer);

        foreach ($schema[$typeName] as $index => $typeSchema) {
            $internalTypePointer = $typePointer->withPathElement($index);
            $internalType = $this->resolvingSchemaParser->parsePointedSchema($specification, $internalTypePointer);

            $this->validateInternalType($typeName, $internalType, $internalTypePointer);

            $type->types->add($internalType);
        }

        return $type;
    }

    private function validateSchema(array $schema, string $typeName, SpecificationPointer $pointer): void
    {
        if (!\is_array($schema[$typeName]) || \count($schema[$typeName]) === 0) {
            throw new ParsingException('Value must be not empty array', $pointer);
        }
    }

    private function getSchemaTypeName(array $schema, SpecificationPointer $pointer): string
    {
        $typeName = null;

        foreach (array_keys(self::COMBINED_TYPES) as $combinedTypeName) {
            if (array_key_exists($combinedTypeName, $schema)) {
                $typeName = $combinedTypeName;

                break;
            }
        }

        if ($typeName === null) {
            throw new ParsingException('Not supported combined type, must be one of: "oneOf", "allOf" or "anyOf"', $pointer);
        }

        return $typeName;
    }

    private function createCombinedType(string $typeName): AbstractCombinedType
    {
        $typeClass = self::COMBINED_TYPES[$typeName];

        return new $typeClass;
    }

    private function validateInternalType(string $typeName, SpecificationObjectMarkerInterface $internalType, SpecificationPointer $internalTypePointer): void
    {
        if (($typeName === 'anyOf' || $typeName === 'allOf') && !$internalType instanceof ObjectType) {
            throw new ParsingException('All internal types of "anyOf" or "allOf" schema must be objects', $internalTypePointer);
        }
    }
}
