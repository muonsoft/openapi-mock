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

use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\Servers;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointSchemaContext
{
    /** @var string */
    private $path;

    /** @var string */
    private $tag;

    /** @var EndpointParameterCollection */
    private $parameters;

    /** @var Servers */
    private $servers;

    public function __construct(string $path, string $tag, EndpointParameterCollection $parameters, Servers $servers)
    {
        $this->path = $path;
        $this->tag = $tag;
        $this->parameters = $parameters;
        $this->servers = $servers;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getParameters(): EndpointParameterCollection
    {
        return $this->parameters;
    }

    public function getServers(): Servers
    {
        return $this->servers;
    }
}
