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
use App\Mock\Parameters\MockResponseCollection;
use PHPUnit\Framework\TestCase;

class MockParametersRepositoryTest extends TestCase
{
    private const PATH = 'path';
    private const HTTP_METHOD = 'http_method';

    /** @test */
    public function findMockParameters_existingPathAndHttpMethod_mockParametersReturned(): void
    {
        $parametersCollection = $this->givenMockParametersCollection();
        $repository = new MockParametersRepository($parametersCollection);

        $parameters = $repository->findMockParameters(self::HTTP_METHOD, self::PATH);

        $this->assertNotNull($parameters);
        $this->assertSame(self::PATH, $parameters->path);
        $this->assertSame(self::HTTP_METHOD, $parameters->httpMethod);
    }

    /** @test */
    public function findMockParameters_notExistingPathAndHttpMethod_nullReturned(): void
    {
        $parametersCollection = $this->givenMockParametersCollection();
        $repository = new MockParametersRepository($parametersCollection);

        $parameters = $repository->findMockParameters('', '');

        $this->assertNull($parameters);
    }

    private function givenMockParametersCollection(): MockParametersCollection
    {
        $parameters = new MockParameters();
        $parameters->path = self::PATH;
        $parameters->httpMethod = self::HTTP_METHOD;

        return new MockParametersCollection([$parameters]);
    }
}
