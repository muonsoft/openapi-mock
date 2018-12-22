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
class SpecificationAccessor
{
    /** @var array */
    private $specification;

    public function __construct(array $specification)
    {
        $this->specification = $specification;
    }

    public function getSchema(SpecificationPointer $pointer): array
    {
        $schema = $this->specification;

        $pathElements = $pointer->getPathElements();

        foreach ($pathElements as $pathElement) {
            if (array_key_exists($pathElement, $schema)) {
                $schema = $schema[$pathElement];
            } else {
                $schema = [];

                break;
            }
        }

        return $schema;
    }
}
