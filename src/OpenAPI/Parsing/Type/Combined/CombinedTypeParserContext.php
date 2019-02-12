<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type\Combined;

use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class CombinedTypeParserContext
{
    /** @var SpecificationAccessor */
    public $specification;

    /** @var string */
    public $typeName;

    /** @var array */
    public $schema;

    /** @var SpecificationPointer */
    public $typePointer;

    public function __construct(SpecificationAccessor $specification, string $typeName, array $schema, SpecificationPointer $typePointer)
    {
        $this->specification = $specification;
        $this->typeName = $typeName;
        $this->schema = $schema;
        $this->typePointer = $typePointer;
    }
}
