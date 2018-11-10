<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Utility\TestCase;

use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait LoggerTestCaseTrait
{
    /** @var LoggerInterface */
    protected $logger;
    
    protected function setUpLogger(): void
    {
        $this->logger = \Phake::mock(LoggerInterface::class);
    }

    protected function assertLogger_debug_wasCalledTimes(int $times): void
    {
        \Phake::verify($this->logger, \Phake::times($times))
            ->debug(\Phake::anyParameters());
    }

    protected function assertLogger_info_wasCalledOnce(string $message = null): void
    {
        $params = $message ?? \Phake::anyParameters();

        \Phake::verify($this->logger, \Phake::times(1))
            ->info($params);
    }

    protected function assertLogger_info_wasCalledTimes(int $times): void
    {
        \Phake::verify($this->logger, \Phake::times($times))
            ->info(\Phake::anyParameters());
    }

    protected function assertLogger_warning_wasCalledOnce(string $message = null): void
    {
        $params = $message ?? \Phake::anyParameters();

        \Phake::verify($this->logger, \Phake::times(1))
            ->warning($params);
    }

    protected function assertLogger_warning_wasCalledTimes(int $times): void
    {
        \Phake::verify($this->logger, \Phake::times($times))
            ->warning(\Phake::anyParameters());
    }

    protected function assertLogger_warning_wasNeverCalled(): void
    {
        \Phake::verify($this->logger, \Phake::times(0))
            ->warning(\Phake::anyParameters());
    }

    protected function assertLogger_error_wasCalledOnce(string $message = null): void
    {
        $params = $message ?? \Phake::anyParameters();

        \Phake::verify($this->logger, \Phake::times(1))
            ->error($params);
    }

    protected function assertLogger_critical_wasCalledOnce(string $message = null): void
    {
        $params = $message ?? \Phake::anyParameters();

        \Phake::verify($this->logger, \Phake::times(1))
            ->critical($params);
    }
}
