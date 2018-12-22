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

use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface ContextualParserInterface
{
    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface;
}
