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
use App\OpenAPI\Parsing\ContextualParserInterface;
use App\OpenAPI\Parsing\SpecificationPointer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface TypeParserInterface extends ContextualParserInterface
{
    public function parsePointedSchema(array $schema, SpecificationPointer $pointer): TypeMarkerInterface;
}
