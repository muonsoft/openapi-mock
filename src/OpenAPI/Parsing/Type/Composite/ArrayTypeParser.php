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
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\ParsingException;
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

    public function parse(array $schema, ParsingContext $context): TypeMarkerInterface
    {
        $this->validateSchema($schema, $context);

        $type = new ArrayType();
        $itemsContext = $context->withSubPath('items');
        $type->items = $this->schemaTransformingParser->parse($schema['items'], $itemsContext);

        return $type;
    }

    private function validateSchema(array $schema, ParsingContext $context): void
    {
        if (!array_key_exists('items', $schema)) {
            throw new ParsingException('Section "items" is required', $context);
        }
    }
}
