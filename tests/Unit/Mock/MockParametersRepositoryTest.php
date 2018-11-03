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

use App\Mock\MockParametersRepository;
use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockParametersCollection;
use App\Tests\Utility\TestCase\SpecificationLoaderTestCaseTrait;
use PHPUnit\Framework\TestCase;

class MockParametersRepositoryTest extends TestCase
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
    public function findMockParameters_existingPathAndHttpMethod_mockParametersReturned(): void
    {
        $repository = $this->createMockParametersRepository();
        $parametersCollection = $this->givenMockParametersCollection();
        $this->givenSpecificationLoader_loadMockParameters_returnsMockParametersCollection($parametersCollection);

        $parameters = $repository->findMockParameters(self::HTTP_METHOD, self::PATH);

        $this->assertNotNull($parameters);
        $this->assertSpecificationLoader_loadMockParameters_wasCalledOnceWithUrl(self::SPECIFICATION_URL);
        $this->assertSame(self::PATH, $parameters->path);
        $this->assertSame(self::HTTP_METHOD, $parameters->httpMethod);
    }

    /** @test */
    public function findMockParameters_notExistingPathAndHttpMethod_nullReturned(): void
    {
        $repository = $this->createMockParametersRepository();
        $parametersCollection = $this->givenMockParametersCollection();
        $this->givenSpecificationLoader_loadMockParameters_returnsMockParametersCollection($parametersCollection);

        $parameters = $repository->findMockParameters('', '');

        $this->assertNull($parameters);
        $this->assertSpecificationLoader_loadMockParameters_wasCalledOnceWithUrl(self::SPECIFICATION_URL);
    }

    private function createMockParametersRepository(): MockParametersRepository
    {
        return new MockParametersRepository($this->specificationLoader, self::SPECIFICATION_URL);
    }

    private function givenMockParametersCollection(): MockParametersCollection
    {
        $parameters = new MockParameters();
        $parameters->path = self::PATH;
        $parameters->httpMethod = self::HTTP_METHOD;

        return new MockParametersCollection([$parameters]);
    }
}
