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

use App\Mock\Parameters\Servers;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class PathContext implements ContextMarkerInterface
{
    /** @var string */
    private $path;

    /** @var Servers */
    private $servers;

    public function __construct(string $path, Servers $servers)
    {
        $this->path = $path;
        $this->servers = $servers;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getServers(): Servers
    {
        return $this->servers;
    }
}
