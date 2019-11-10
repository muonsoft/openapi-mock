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

use App\Enum\HttpMethodEnum;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\Servers;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointContext implements ContextMarkerInterface
{
    /** @var string */
    private $path;

    /** @var HttpMethodEnum */
    private $httpMethod;

    /** @var EndpointParameterCollection */
    private $parameters;

    /** @var Servers */
    private $servers;

    public function __construct(
        string $path,
        HttpMethodEnum $httpMethod,
        EndpointParameterCollection $parameters,
        Servers $servers
    ) {
        $this->path = $path;
        $this->httpMethod = $httpMethod;
        $this->parameters = $parameters;
        $this->servers = $servers;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHttpMethod(): HttpMethodEnum
    {
        return $this->httpMethod;
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
