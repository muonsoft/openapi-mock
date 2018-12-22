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

use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\SpecificationPointer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SchemaTransformingParser implements TypeParserInterface
{
    /** @var TypeParserLocator */
    private $typeParserLocator;

    public function __construct(TypeParserLocator $typeParserLocator)
    {
        $this->typeParserLocator = $typeParserLocator;
    }

    public function parse(array $schema, SpecificationPointer $pointer): TypeMarkerInterface
    {
        $typeParser = $this->typeParserLocator->getTypeParser($schema['type']);

        return $typeParser->parse($schema, $pointer);
    }
}
