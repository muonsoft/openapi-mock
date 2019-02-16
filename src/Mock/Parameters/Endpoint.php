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

use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Endpoint implements SpecificationObjectMarkerInterface
{
    /** @var string */
    public $httpMethod;

    /** @var string */
    public $path;

    /** @var MockResponseCollection */
    public $responses;

    /** @var EndpointParameterCollection */
    public $parameters;

    public function __construct()
    {
        $this->responses = new MockResponseCollection();
        $this->parameters = new EndpointParameterCollection();
    }
}
