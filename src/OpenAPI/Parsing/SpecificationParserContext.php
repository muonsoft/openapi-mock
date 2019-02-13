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

use App\Mock\Parameters\MockParametersCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SpecificationParserContext
{
    /** @var SpecificationAccessor */
    public $specification;

    /** @var MockParametersCollection */
    public $mockParametersCollection;

    /** @var SpecificationPointer */
    public $pathPointer;

    /** @var string */
    public $path;

    /** @var SpecificationPointer */
    public $endpointPointer;

    public function __construct(SpecificationAccessor $specification)
    {
        $this->specification = $specification;
        $this->mockParametersCollection = new MockParametersCollection();
    }
}
