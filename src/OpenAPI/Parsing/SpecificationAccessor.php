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
class SpecificationAccessor
{
    /** @var array */
    private $specification;

    /** @var SpecificationObjectMarkerInterface[] */
    private $resolvedObjects = [];

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

        if (!is_array($schema)) {
            throw new ParsingException('Schema is expected to be an array or an object', $pointer);
        }

        return $schema;
    }

    public function findResolvedObject(string $reference): ?SpecificationObjectMarkerInterface
    {
        $object = null;

        if (array_key_exists($reference, $this->resolvedObjects)) {
            $object = $this->resolvedObjects[$reference];
        }

        return $object;
    }

    public function setResolvedObject(string $reference, SpecificationObjectMarkerInterface $object): void
    {
        $this->resolvedObjects[$reference] = $object;
    }
}
