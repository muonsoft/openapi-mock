<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Exception;

use App\Mock\Exception\InvalidTypeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidTypeExceptionTest extends TestCase
{
    /** @test */
    public function construct_message_messageIsSetAndStatusCodeIs500(): void
    {
        $exception = new InvalidTypeException();

        $this->assertSame('Trying to use invalid type for generation', $exception->getMessage());
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getStatusCode());
    }
}
