<?php

namespace App\Tests\Acceptance;

use Behat\Behat\ApplicationFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

class AcceptanceBridgeTest extends TestCase
{
    /** @test */
    public function runAcceptanceTests(): void
    {
        try {
            $input = new ArrayInput(['behat', '--format' => ['progress']]);

            $factory = new ApplicationFactory();
            $application = $factory->createApplication();
            $application->setAutoExit(false);
            $result = $application->run($input);

            $this->assertSame(0, $result);
        } catch (\Throwable $exception) {
            $this->fail($exception->getMessage());
        }
    }
}
