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

use App\Mock\Parameters\MockParametersCollection;
use App\OpenAPI\SpecificationLoaderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait SpecificationLoaderTestCaseTrait
{
    /** @var SpecificationLoaderInterface */
    protected $specificationLoader;

    protected function setUpSpecificationLoader(): void
    {
        $this->specificationLoader = \Phake::mock(SpecificationLoaderInterface::class);
    }

    protected function assertSpecificationLoader_loadMockParameters_wasCalledOnceWithUrl(string $url): void
    {
        \Phake::verify($this->specificationLoader)
            ->loadMockParameters($url);
    }

    protected function givenSpecificationLoader_loadMockParameters_returnsMockParametersCollection(
        MockParametersCollection $collection
    ): void {
        \Phake::when($this->specificationLoader)
            ->loadMockParameters(\Phake::anyParameters())
            ->thenReturn($collection);
    }
}
