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

use App\Mock\Parameters\EndpointCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathCollectionParserContext
{
    /** @var SpecificationAccessor */
    public $specification;

    /** @var EndpointCollection */
    public $endpoints;

    /** @var SpecificationPointer */
    public $pathPointer;

    /** @var string */
    public $path;

    /** @var SpecificationPointer */
    public $endpointPointer;

    public function __construct(SpecificationAccessor $specification)
    {
        $this->specification = $specification;
        $this->endpoints = new EndpointCollection();
    }
}
