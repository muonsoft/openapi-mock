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
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\SchemaTransformingParser;
use App\OpenAPI\Parsing\Type\TypeParserInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayTypeParser implements TypeParserInterface
{
    /** @var SchemaTransformingParser */
    private $schemaTransformingParser;

    public function __construct(SchemaTransformingParser $schemaTransformingParser)
    {
        $this->schemaTransformingParser = $schemaTransformingParser;
    }

    public function parsePointedSchema(array $schema, SpecificationPointer $pointer): TypeMarkerInterface
    {
        $this->validateSchema($schema, $pointer);

        $type = new ArrayType();
        $type->items = $this->readItemsSchema($schema, $pointer);
        $type->minItems = $this->readIntegerValue($schema, 'minItems');
        $type->maxItems = $this->readIntegerValue($schema, 'maxItems');
        $type->uniqueItems = $this->readBoolValue($schema, 'uniqueItems');

        return $type;
    }

    private function validateSchema(array $schema, SpecificationPointer $context): void
    {
        if (!array_key_exists('items', $schema)) {
            throw new ParsingException('Section "items" is required', $context);
        }
    }

    private function readItemsSchema(array $schema, SpecificationPointer $context): TypeMarkerInterface
    {
        $itemsContext = $context->withPathElement('items');
        $itemsSchema = $this->schemaTransformingParser->parsePointedSchema($schema['items'], $itemsContext);

        return $itemsSchema;
    }

    private function readBoolValue(array $schema, string $key): bool
    {
        return (bool) ($schema[$key] ?? false);
    }

    private function readIntegerValue(array $schema, string $key): int
    {
        return (int) ($schema[$key] ?? 0);
    }
}
