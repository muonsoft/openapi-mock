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

    public function __construct(string $path, HttpMethodEnum $httpMethod, EndpointParameterCollection $parameters)
    {
        $this->path = $path;
        $this->httpMethod = $httpMethod;
        $this->parameters = $parameters;
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
}
