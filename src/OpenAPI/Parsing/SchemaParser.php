<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing;

use App\Mock\Parameters\Schema\Schema;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SchemaParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $delegatingSchemaParser;

    public function __construct(ContextualParserInterface $delegatingSchemaParser)
    {
        $this->delegatingSchemaParser = $delegatingSchemaParser;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $schema = $specification->getSchema($pointer);
        $schemaType = new Schema();
        $this->validateSchema($schema, $pointer);

        $schemaContext = $pointer->withPathElement('schema');
        $schemaType->value = $this->delegatingSchemaParser->parsePointedSchema($specification, $schemaContext);

        return $schemaType;
    }

    private function validateSchema(array $schema, SpecificationPointer $context): void
    {
        if (!\array_key_exists('schema', $schema)) {
            throw new ParsingException('Invalid schema', $context);
        }
    }
}
