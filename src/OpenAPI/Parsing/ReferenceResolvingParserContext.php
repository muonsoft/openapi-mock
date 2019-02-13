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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ReferenceResolvingParserContext
{
    /** @var SpecificationAccessor */
    public $specification;

    /** @var SpecificationPointer */
    public $pointer;

    /** @var ContextualParserInterface */
    public $parser;

    public function __construct(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ContextualParserInterface $parser
    ) {
        $this->specification = $specification;
        $this->pointer = $pointer;
        $this->parser = $parser;
    }
}
