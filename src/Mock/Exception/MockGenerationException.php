<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockGenerationException extends HttpException
{
    public function __construct(string $message = null, \Exception $previous = null)
    {
        parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $message, $previous);
    }
}
