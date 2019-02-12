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
use App\Mock\Parameters\Schema\Type\InvalidType;
use App\OpenAPI\Parsing\ContextualParserInterface;
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
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

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    public function __construct(ContextualParserInterface $resolvingSchemaParser, ParsingErrorHandlerInterface $errorHandler)
    {
        $this->resolvingSchemaParser = $resolvingSchemaParser;
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $schema = $specification->getSchema($pointer);

        $typeName = $this->findSchemaTypeName($schema);

        if ($typeName === null) {
            $type = $this->createInvalidType('Not supported combined type, must be one of: "oneOf", "allOf" or "anyOf"', $pointer);
        } else {
            $typePointer = $pointer->withPathElement($typeName);
            $context = new CombinedTypeParserContext($specification, $typeName, $schema, $typePointer);
            $type = $this->validateAndParseCombinedType($context);
        }

        return $type;
    }

    private function findSchemaTypeName(array $schema): ?string
    {
        $typeName = null;

        foreach (array_keys(self::COMBINED_TYPES) as $combinedTypeName) {
            if (array_key_exists($combinedTypeName, $schema)) {
                $typeName = $combinedTypeName;

                break;
            }
        }

        return $typeName;
    }

    private function validateAndParseCombinedType(CombinedTypeParserContext $context): SpecificationObjectMarkerInterface
    {
        $error = $this->validateSchema($context->schema, $context->typeName);

        if ($error !== null) {
            $type = $this->createInvalidType($error, $context->typePointer);
        } else {
            $type = $this->parseCombinedType($context);
        }

        return $type;
    }

    private function parseCombinedType(CombinedTypeParserContext $context): AbstractCombinedType
    {
        $type = $this->createCombinedType($context->typeName);

        foreach (array_keys($context->schema[$context->typeName]) as $index) {
            $internalTypePointer = $context->typePointer->withPathElement($index);
            $internalType = $this->resolvingSchemaParser->parsePointedSchema($context->specification, $internalTypePointer);

            $isValid = $this->validateInternalType($context->typeName, $internalType, $internalTypePointer);

            if ($isValid) {
                $type->types->add($internalType);
            }
        }

        return $type;
    }

    private function validateSchema(array $schema, string $typeName): ?string
    {
        $error = null;

        if (!\is_array($schema[$typeName]) || 0 === \count($schema[$typeName])) {
            $error = 'Value must be not empty array';
        }

        return $error;
    }

    private function validateInternalType(string $typeName, SpecificationObjectMarkerInterface $internalType, SpecificationPointer $internalTypePointer): bool
    {
        $isValid = 'oneOf' === $typeName || $internalType instanceof ObjectType;

        if (!$isValid) {
            $this->errorHandler->reportError('All internal types of "anyOf" or "allOf" schema must be objects', $internalTypePointer);
        }

        return $isValid;
    }

    private function createCombinedType(string $typeName): AbstractCombinedType
    {
        $typeClass = self::COMBINED_TYPES[$typeName];

        return new $typeClass();
    }

    private function createInvalidType(string $message, SpecificationPointer $pointer): InvalidType
    {
        $error = $this->errorHandler->reportError($message, $pointer);

        return new InvalidType($error);
    }
}
