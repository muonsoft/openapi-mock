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

use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\InvalidType;
use App\OpenAPI\Parsing\ContextualParserInterface;
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\FieldParserTrait;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayTypeParser implements TypeParserInterface
{
    use FieldParserTrait;

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
        $error = $this->validateSchema($schema, $pointer);

        if (null === $error) {
            $type = $this->parseArraySchema($specification, $pointer, $schema);
        } else {
            $type = new InvalidType($error);
        }

        return $type;
    }

    private function validateSchema(array $schema, SpecificationPointer $pointer): ?string
    {
        $error = null;

        if (!array_key_exists('items', $schema)) {
            $error = $this->errorHandler->reportError('Section "items" is required', $pointer);
        }

        return $error;
    }

    private function parseArraySchema(SpecificationAccessor $specification, SpecificationPointer $pointer, array $schema): ArrayType
    {
        $type = new ArrayType();
        $this->readFixedFieldsValues($type, $schema);
        $type->items = $this->readItemsSchema($specification, $pointer);
        $type->minItems = $this->readIntegerValue($schema, 'minItems');
        $type->maxItems = $this->readIntegerValue($schema, 'maxItems');
        $type->uniqueItems = $this->readBoolValue($schema, 'uniqueItems');

        return $type;
    }

    private function readItemsSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $itemsPointer = $pointer->withPathElement('items');

        return $this->resolvingSchemaParser->parsePointedSchema($specification, $itemsPointer);
    }
}
