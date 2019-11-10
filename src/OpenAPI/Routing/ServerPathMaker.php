<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Routing;

use App\Mock\Parameters\Servers;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ServerPathMaker
{
    public function createServerPaths(Servers $servers): array
    {
        $paths = [];

        foreach ($servers->urls as $url) {
            $path = trim(parse_url($url, PHP_URL_PATH));
            $paths[] = '/'.ltrim($path, '/');
        }

        return array_unique($paths);
    }
}
