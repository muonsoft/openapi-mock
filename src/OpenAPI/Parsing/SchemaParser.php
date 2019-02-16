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
use App\Mock\Parameters\Schema\Type\InvalidType;
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SchemaParser implements ParserInterface
{
    /** @var ParserInterface */
    private $resolvingSchemaParser;

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    public function __construct(ParserInterface $resolvingSchemaParser, ParsingErrorHandlerInterface $errorHandler)
    {
        $this->resolvingSchemaParser = $resolvingSchemaParser;
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $schema = $specification->getSchema($pointer);
        $schemaType = new Schema();
        $error = $this->validateSchema($schema, $pointer);

        if ($error) {
            $schemaType->value = new InvalidType($error);
        } else {
            $schemaPointer = $pointer->withPathElement('schema');
            $schemaType->value = $this->resolvingSchemaParser->parsePointedSchema($specification, $schemaPointer);
        }

        return $schemaType;
    }

    private function validateSchema(array $schema, SpecificationPointer $pointer): ?string
    {
        $error = null;

        if (!\array_key_exists('schema', $schema)) {
            $error = $this->errorHandler->reportError('Invalid schema', $pointer);
        }

        return $error;
    }
}
