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
class SpecificationPointer
{
    private const PATH_DELIMITER = ' -> ';

    /** @var string[] */
    private $pathElements;

    public function __construct(array $pathElements = [])
    {
        $this->pathElements = $pathElements;
    }

    public function getPathElements(): array
    {
        return $this->pathElements;
    }

    public function getPath(): string
    {
        return implode(self::PATH_DELIMITER, $this->pathElements);
    }

    public function addPathElement(string $pathElement): void
    {
        $this->pathElements[] = $pathElement;
    }

    public function withPathElement(string $pathElement): self
    {
        $newPointer = clone $this;
        $newPointer->addPathElement($pathElement);

        return $newPointer;
    }
}
