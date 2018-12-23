<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type;

use App\OpenAPI\Parsing\ContextualParserInterface;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class DelegatingSchemaParser implements ContextualParserInterface
{
    private const COMBINED_TYPES = [
        'oneOf',
        'anyOf',
        'allOf',
    ];

    /** @var TypeParserLocator */
    private $typeParserLocator;

    public function __construct(TypeParserLocator $typeParserLocator)
    {
        $this->typeParserLocator = $typeParserLocator;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $schema = $specification->getSchema($pointer);
        $type = $this->getSchemaType($schema, $pointer);
        $typeParser = $this->typeParserLocator->getTypeParser($type);

        return $typeParser->parsePointedSchema($specification, $pointer);
    }

    private function getSchemaType(array $schema, SpecificationPointer $pointer): string
    {
        $type = $this->detectSchemaType($schema);

        if ($type === null) {
            throw new ParsingException(
                'Invalid schema: must contain one of properties: "type", "oneOf", "anyOf" or "allOf".',
                $pointer
            );
        }

        return $type;
    }

    private function detectSchemaType(array $schema): ?string
    {
        $type = null;

        if (array_key_exists('type', $schema)) {
            $type = $schema['type'];
        } else {
            foreach (self::COMBINED_TYPES as $combinedType) {
                if (array_key_exists($combinedType, $schema)) {
                    $type = $combinedType;

                    break;
                }
            }
        }

        return $type;
    }
}
