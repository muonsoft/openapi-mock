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
use App\OpenAPI\Parsing\ContextualParserInterface;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\DelegatingSchemaParser;
use App\OpenAPI\Parsing\Type\FieldParserTrait;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayTypeParser implements TypeParserInterface
{
    use FieldParserTrait;

    /** @var DelegatingSchemaParser */
    private $resolvingSchemaParser;

    public function __construct(ContextualParserInterface $resolvingSchemaParser)
    {
        $this->resolvingSchemaParser = $resolvingSchemaParser;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $schema = $specification->getSchema($pointer);
        $this->validateSchema($schema, $pointer);

        $type = new ArrayType();
        $this->readFixedFieldsValues($type, $schema);
        $type->items = $this->readItemsSchema($specification, $pointer);
        $type->minItems = $this->readIntegerValue($schema, 'minItems');
        $type->maxItems = $this->readIntegerValue($schema, 'maxItems');
        $type->uniqueItems = $this->readBoolValue($schema, 'uniqueItems');

        return $type;
    }

    private function validateSchema(array $schema, SpecificationPointer $pointer): void
    {
        if (!array_key_exists('items', $schema)) {
            throw new ParsingException('Section "items" is required', $pointer);
        }
    }

    private function readItemsSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $itemsPointer = $pointer->withPathElement('items');

        return $this->resolvingSchemaParser->parsePointedSchema($specification, $itemsPointer);
    }
}
