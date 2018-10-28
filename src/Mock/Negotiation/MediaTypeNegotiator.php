<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Negotiation;

use App\Mock\Parameters\MockParameters;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MediaTypeNegotiator
{
    public function negotiateMediaType(Request $request, MockParameters $parameters): string
    {
        return 'application/json';
    }
}
