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
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseStatusNegotiator
{
    public function negotiateResponseStatus(Request $request, MockParameters $parameters): int
    {
        return Response::HTTP_OK;
    }
}
