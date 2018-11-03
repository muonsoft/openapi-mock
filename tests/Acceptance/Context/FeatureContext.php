<?php

namespace App\Tests\Acceptance\Context;

use App\Tests\Utility\MockedSpecificationLoader;
use Behat\Behat\Context\Context;

class FeatureContext implements Context
{
    /** @var MockedSpecificationLoader */
    private $specificationLoader;

    /** @var string */
    private $swaggerFilesDirectory;

    public function __construct(MockedSpecificationLoader $specificationLoader, string $swaggerFilesDirectory)
    {
        $this->specificationLoader = $specificationLoader;
        $this->swaggerFilesDirectory = $swaggerFilesDirectory;
    }

    /**
     * @Given I have OpenAPI specification file :filename
     */
    public function iHaveOpenApiSpecificationFile(string $filename): void
    {
        $swaggerFile = $this->swaggerFilesDirectory . $filename;
        $this->specificationLoader->setSpecificationFilename($swaggerFile);
    }
}
