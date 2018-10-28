<?php

namespace App\Tests\Acceptance\Context;

use App\OpenAPI\SpecificationLoader;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class FeatureContext implements KernelAwareContext
{
    /** @var KernelInterface */
    private $kernel;

    /** @var ContainerInterface */
    private $container;

    /** @var SpecificationLoader */
    private $specificationLoader;

    /** @var string */
    private $swaggerFilesDirectory;

    public function __construct(KernelInterface $kernel, SpecificationLoader $specificationLoader)
    {
        $this->kernel = $kernel;

        $this->container = $kernel->getContainer();
        $this->specificationLoader = $specificationLoader;
//        $this->specificationLoader = $this->container->get(SpecificationLoader::class);
        $this->swaggerFilesDirectory = sprintf(
            '%s/tests/Resources/swagger-files/',
            $this->container->getParameter('kernel.project_dir')
        );
    }

    public function setKernel(KernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given I have OpenAPI specification file :filename
     */
    public function iHaveOpenApiSpecificationFile(string $filename): void
    {
        $swaggerFile = $this->swaggerFilesDirectory . $filename;
        $mockParametersCollection = $this->specificationLoader->loadMockParameters($swaggerFile);
        $this->container->set('mock_parameters_collection', $mockParametersCollection);
    }
}
