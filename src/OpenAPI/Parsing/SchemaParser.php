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
use App\OpenAPI\Parsing\Type\SchemaTransformingParser;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SchemaParser implements ContextualParserInterface
{
    /** @var SchemaTransformingParser */
    private $schemaTransformingParser;

    public function __construct(SchemaTransformingParser $schemaTransformingParser)
    {
        $this->schemaTransformingParser = $schemaTransformingParser;
    }

    public function parse(array $schema, ParsingContext $context): Schema
    {
        $parsedSchema = new Schema();
        $this->validateSchema($schema, $context);

        $schemaContext = $context->withSubPath('schema');
        $parsedSchema->value = $this->schemaTransformingParser->parse($schema['schema'], $schemaContext);

        return $parsedSchema;
    }

    private function validateSchema(array $schema, ParsingContext $context): void
    {
        if (!\array_key_exists('schema', $schema)) {
            throw new ParsingException('Invalid schema', $context);
        }
    }
}
