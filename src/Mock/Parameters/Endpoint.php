<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters;

use App\Enum\HttpMethodEnum;
use App\OpenAPI\Routing\NullUrlMatcher;
use App\OpenAPI\Routing\UrlMatcherInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Endpoint implements SpecificationObjectMarkerInterface
{
    /** @var HttpMethodEnum */
    public $httpMethod;

    /** @var string */
    public $path;

    /** @var MockResponseMap */
    public $responses;

    /** @var EndpointParameterCollection */
    public $parameters;

    /** @var UrlMatcherInterface */
    public $urlMatcher;

    public function __construct()
    {
        $this->responses = new MockResponseMap();
        $this->parameters = new EndpointParameterCollection();
        $this->urlMatcher = new NullUrlMatcher();
    }
}
