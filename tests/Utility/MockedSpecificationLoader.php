<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Utility;

use App\Mock\Parameters\MockParametersCollection;
use App\OpenAPI\SpecificationLoaderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockedSpecificationLoader implements SpecificationLoaderInterface
{
    /** @var self */
    private static $instance;

    /** @var SpecificationLoaderInterface */
    private $specificationLoader;

    /** @var string */
    private $specificationFilename;

    public function __construct(SpecificationLoaderInterface $specificationLoader)
    {
        $this->specificationLoader = $specificationLoader;
    }

    public static function getInstance(SpecificationLoaderInterface $specificationLoader): self
    {
        if (null === self::$instance) {
            self::$instance = new self($specificationLoader);
        }

        return self::$instance;
    }

    public function setSpecificationFilename(string $specificationFilename): void
    {
        $this->specificationFilename = $specificationFilename;
    }

    public function loadMockParameters(string $url): MockParametersCollection
    {
        return $this->specificationLoader->loadMockParameters($this->specificationFilename);
    }
}
