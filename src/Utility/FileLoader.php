<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utility;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class FileLoader
{
    public function loadFileContents(string $uri): string
    {
        return file_get_contents($uri);
    }
}
