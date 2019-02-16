<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum;

use MyCLabs\Enum\Enum;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParameterLocationEnum extends Enum
{
    public const QUERY = 'query';
    public const PATH = 'path';
    public const HEADER = 'header';
    public const COOKIE = 'cookie';
}
