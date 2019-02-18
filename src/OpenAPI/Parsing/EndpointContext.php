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
    public $path;

    /** @var HttpMethodEnum */
    public $httpMethod;

    /** @var EndpointParameterCollection */
    public $parameters;

    public function __construct()
    {
        $this->parameters = new EndpointParameterCollection();
    }
}
