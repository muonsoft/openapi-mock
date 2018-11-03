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
class ParsingContext
{
    private const PATH_DELIMITER = '.';

    /** @var string[] */
    private $path = [];

    public function getPath(): string
    {
        return implode(self::PATH_DELIMITER, $this->path);
    }

    public function addSubPath(string $subPath): void
    {
        $this->path[] = $subPath;
    }

    public function withSubPath(string $path): ParsingContext
    {
        $newContext = clone $this;
        $newContext->addSubPath($path);

        return $newContext;
    }
}
