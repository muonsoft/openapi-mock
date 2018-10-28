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
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\Parsing\TypeParserLocator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayTypeParser implements TypeParserInterface
{
    /** @var TypeParserLocator */
    private $typeParserLocator;

    public function __construct(TypeParserLocator $typeParserLocator)
    {
        $this->typeParserLocator = $typeParserLocator;
    }

    public function parseTypeSchema(array $schema): TypeMarkerInterface
    {
        $this->validateSchema($schema);

        $type = new ArrayType();
        $type->items = $this->parseItemsSchema($schema['items']);

        return $type;
    }

    private function validateSchema(array $schema): void
    {
        if (!array_key_exists('items', $schema)) {
            throw new ParsingException('Items schema is required');
        }
    }

    private function parseItemsSchema(array $schema): TypeMarkerInterface
    {
        $typeParser = $this->typeParserLocator->getTypeParser($schema['type']);

        return $typeParser->parseTypeSchema($schema);
    }
}
