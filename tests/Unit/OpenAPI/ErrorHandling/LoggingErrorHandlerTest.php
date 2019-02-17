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

use App\OpenAPI\ErrorHandling\LoggingErrorHandler;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\LoggerTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class LoggingErrorHandlerTest extends TestCase
{
    use LoggerTestCaseTrait;

    private const ERROR_MESSAGE = 'Parsing error "message" at path "path".';

    protected function setUp(): void
    {
        $this->setUpLogger();
    }

    /** @test */
    public function reportError_messageAndPointer_errorLogged(): void
    {
        $handler = $this->createLoggingErrorHandler();
        $pointer = new SpecificationPointer(['path']);

        $error = $handler->reportError('message', $pointer);

        $this->assertLogger_error_wasCalledOnce(self::ERROR_MESSAGE);
        $this->assertSame(self::ERROR_MESSAGE, $error);
    }

    /** @test */
    public function reportWarning_messageAndPointer_warningLogged(): void
    {
        $handler = $this->createLoggingErrorHandler();
        $pointer = new SpecificationPointer(['path']);

        $warning = $handler->reportWarning('message', $pointer);

        $this->assertLogger_warning_wasCalledOnce(self::ERROR_MESSAGE);
        $this->assertSame(self::ERROR_MESSAGE, $warning);
    }

    private function createLoggingErrorHandler(): LoggingErrorHandler
    {
        return new LoggingErrorHandler($this->logger);
    }
}
