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

use App\Enum\EndpointParameterLocationEnum;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParameter
{
    /** @var string */
    public $name;

    /** @var EndpointParameterLocationEnum */
    public $in;

    /** @var bool */
    public $required;

    /** @var TypeInterface */
    public $schema;
}
