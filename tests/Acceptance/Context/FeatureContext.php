<?php

namespace App\Tests\Acceptance\Context;

use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class FeatureContext implements KernelAwareContext
{
    /** @var KernelInterface */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    private function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }
}
