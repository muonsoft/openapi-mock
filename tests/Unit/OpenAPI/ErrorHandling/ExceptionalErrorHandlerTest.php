<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\ErrorHandling;

use App\OpenAPI\ErrorHandling\ExceptionalErrorHandler;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationPointer;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ExceptionalErrorHandlerTest extends TestCase
{
    /** @test */
    public function reportError_messageAndPointer_exceptionThrown(): void
    {
        $handler = new ExceptionalErrorHandler();
        $pointer = new SpecificationPointer();

        $this->expectException(ParsingException::class);

        $handler->reportError('message', $pointer);
    }

    /** @test */
    public function reportWarning_messageAndPointer_exceptionThrown(): void
    {
        $handler = new ExceptionalErrorHandler();
        $pointer = new SpecificationPointer();

        $this->expectException(ParsingException::class);

        $handler->reportWarning('message', $pointer);
    }
}
