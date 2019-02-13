<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock;

use App\Mock\MockEndpointRepository;
use App\Mock\Parameters\MockEndpoint;
use App\Mock\Parameters\MockEndpointCollection;
use App\Tests\Utility\TestCase\SpecificationLoaderTestCaseTrait;
use PHPUnit\Framework\TestCase;

class MockEndpointRepositoryTest extends TestCase
{
    use SpecificationLoaderTestCaseTrait;

    private const PATH = 'path';
    private const HTTP_METHOD = 'http_method';

    private const SPECIFICATION_URL = 'specification_url';

    protected function setUp(): void
    {
        $this->setUpSpecificationLoader();
    }

    /** @test */
    public function findMockEndpoint_existingPathAndHttpMethod_mockEndpointReturned(): void
    {
        $repository = $this->createMockEndpointRepository();
        $parametersCollection = $this->givenMockEndpointCollection();
        $this->givenSpecificationLoader_loadMockEndpoints_returnsMockEndpointCollection($parametersCollection);

        $parameters = $repository->findMockEndpoint(self::HTTP_METHOD, self::PATH);

        $this->assertNotNull($parameters);
        $this->assertSpecificationLoader_loadMockEndpoints_wasCalledOnceWithUrl(self::SPECIFICATION_URL);
        $this->assertSame(self::PATH, $parameters->path);
        $this->assertSame(self::HTTP_METHOD, $parameters->httpMethod);
    }

    /** @test */
    public function findMockEndpoint_notExistingPathAndHttpMethod_nullReturned(): void
    {
        $repository = $this->createMockEndpointRepository();
        $parametersCollection = $this->givenMockEndpointCollection();
        $this->givenSpecificationLoader_loadMockEndpoints_returnsMockEndpointCollection($parametersCollection);

        $parameters = $repository->findMockEndpoint('', '');

        $this->assertNull($parameters);
        $this->assertSpecificationLoader_loadMockEndpoints_wasCalledOnceWithUrl(self::SPECIFICATION_URL);
    }

    private function createMockEndpointRepository(): MockEndpointRepository
    {
        return new MockEndpointRepository($this->specificationLoader, self::SPECIFICATION_URL);
    }

    private function givenMockEndpointCollection(): MockEndpointCollection
    {
        $parameters = new MockEndpoint();
        $parameters->path = self::PATH;
        $parameters->httpMethod = self::HTTP_METHOD;

        return new MockEndpointCollection([$parameters]);
    }
}
