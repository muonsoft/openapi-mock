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
class HttpMethodEnum extends Enum
{
    public const GET = 'get';
    public const POST = 'post';
    public const PUT = 'put';
    public const PATCH = 'patch';
    public const DELETE = 'delete';
    public const OPTIONS = 'options';
    public const HEAD = 'head';
    public const TRACE = 'trace';
}
