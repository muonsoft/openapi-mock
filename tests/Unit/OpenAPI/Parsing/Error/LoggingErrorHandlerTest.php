<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Error;

use App\OpenAPI\Parsing\Error\LoggingErrorHandler;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\LoggerTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class LoggingErrorHandlerTest extends TestCase
{
    use LoggerTestCaseTrait;

    protected function setUp(): void
    {
        $this->setUpLogger();
    }

    /** @test */
    public function reportError_messageAndPointer_errorLogged(): void
    {
        $handler = $this->createLoggingErrorHandler();
        $pointer = new SpecificationPointer(['path']);

        $handler->reportError('message', $pointer);

        $this->assertLogger_error_wasCalledOnce('Parsing error "message" at path "path".');
    }

    /** @test */
    public function reportWarning_messageAndPointer_warningLogged(): void
    {
        $handler = $this->createLoggingErrorHandler();
        $pointer = new SpecificationPointer(['path']);

        $handler->reportWarning('message', $pointer);

        $this->assertLogger_warning_wasCalledOnce('Parsing error "message" at path "path".');
    }

    private function createLoggingErrorHandler(): LoggingErrorHandler
    {
        return new LoggingErrorHandler($this->logger);
    }
}
