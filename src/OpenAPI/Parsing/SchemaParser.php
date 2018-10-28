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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SchemaParser
{
    /** @var TypeParserLocator */
    private $typeParserLocator;

    public function __construct(TypeParserLocator $typeParserLocator)
    {
        $this->typeParserLocator = $typeParserLocator;
    }

    public function parseSchema(array $schema): Schema
    {
        $parsedSchema = new Schema();
        $this->validateSchema($schema);

        $valueType = $schema['schema']['type'];
        $typeParser = $this->typeParserLocator->getTypeParser($valueType);
        $parsedSchema->value = $typeParser->parseTypeSchema($schema['schema']);

        return $parsedSchema;
    }

    private function validateSchema(array $schema): void
    {
        if (!\array_key_exists('schema', $schema)) {
            throw new ParsingException('Invalid schema');
        }
    }
}
