<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type\Primitive;

use App\Mock\Parameters\Schema\Type\Primitive\BooleanType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\TypeParserInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class BooleanTypeParser implements TypeParserInterface
{
    public function parsePointedSchema(array $schema, SpecificationPointer $pointer): TypeMarkerInterface
    {
        return new BooleanType();
    }
}
