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

use App\Mock\Exception\MockGenerationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class MockGenerationExceptionTest extends TestCase
{
    private const MESSAGE = 'message';

    /** @test */
    public function construct_message_messageIsSetAndStatusCodeIs500(): void
    {
        $exception = new MockGenerationException(self::MESSAGE);

        $this->assertSame(self::MESSAGE, $exception->getMessage());
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getStatusCode());
    }
}
