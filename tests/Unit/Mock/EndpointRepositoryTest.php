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

use App\Enum\HttpMethodEnum;
use App\Mock\EndpointRepository;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointCollection;
use App\OpenAPI\Routing\UrlMatcherInterface;
use App\Tests\Utility\TestCase\SpecificationLoaderTestCaseTrait;
use PHPUnit\Framework\TestCase;

class EndpointRepositoryTest extends TestCase
{
    use SpecificationLoaderTestCaseTrait;

    private const PATH = 'path';
    private const PATH_WITH_TRAILING_SLASH = 'path/';
    private const HTTP_METHOD = 'GET';
    private const SPECIFICATION_URL = 'specification_url';

    /** @var UrlMatcherInterface */
    private $urlMatcher;

    protected function setUp(): void
    {
        $this->setUpSpecificationLoader();
        $this->urlMatcher = \Phake::mock(UrlMatcherInterface::class);
    }

    /** @test */
    public function findMockEndpoint_existingHttpMethodAndUriMatches_mockEndpointReturned(): void
    {
        $repository = $this->createMockEndpointRepository();
        $endpoints = $this->givenEndpointCollection();
        $this->givenSpecificationLoader_loadMockEndpoints_returnsMockEndpointCollection($endpoints);
        $this->givenUrlMatcher_urlIsMatching_returnTrue();

        $parameters = $repository->findMockEndpoint(self::HTTP_METHOD, self::PATH);

        $this->assertNotNull($parameters);
        $this->assertSpecificationLoader_loadMockEndpoints_wasCalledOnceWithUrl(self::SPECIFICATION_URL);
        $this->assertSame(self::PATH, $parameters->path);
        $this->assertSame(strtolower(self::HTTP_METHOD), $parameters->httpMethod->getValue());
        $this->assertUrlMatcher_urlIsMatching_wasCalledOnceWithUrl(self::PATH);
    }

    /** @test */
    public function findMockEndpoint_urlWithTrailingSlash_trailingSlashRemovedForMatcher(): void
    {
        $repository = $this->createMockEndpointRepository();
        $endpoints = $this->givenEndpointCollection();
        $this->givenSpecificationLoader_loadMockEndpoints_returnsMockEndpointCollection($endpoints);
        $this->givenUrlMatcher_urlIsMatching_returnTrue();

        $repository->findMockEndpoint(self::HTTP_METHOD, self::PATH_WITH_TRAILING_SLASH);

        $this->assertUrlMatcher_urlIsMatching_wasCalledOnceWithUrl(self::PATH);
    }

    /** @test */
    public function findMockEndpoint_existingHttpMethodAndUriNotMatches_nullReturned(): void
    {
        $repository = $this->createMockEndpointRepository();
        $endpoints = $this->givenEndpointCollection();
        $this->givenSpecificationLoader_loadMockEndpoints_returnsMockEndpointCollection($endpoints);
        $this->givenUrlMatcher_urlIsMatching_returnFalse();

        $parameters = $repository->findMockEndpoint(self::HTTP_METHOD, '');

        $this->assertNull($parameters);
        $this->assertSpecificationLoader_loadMockEndpoints_wasCalledOnceWithUrl(self::SPECIFICATION_URL);
        $this->assertUrlMatcher_urlIsMatching_wasCalledOnceWithUrl('');
    }

    /** @test */
    public function findMockEndpoint_notExistingHttpMethodAndUriMatches_nullReturned(): void
    {
        $repository = $this->createMockEndpointRepository();
        $endpoints = $this->givenEndpointCollection();
        $this->givenSpecificationLoader_loadMockEndpoints_returnsMockEndpointCollection($endpoints);

        $parameters = $repository->findMockEndpoint('', self::PATH);

        $this->assertNull($parameters);
        $this->assertSpecificationLoader_loadMockEndpoints_wasCalledOnceWithUrl(self::SPECIFICATION_URL);
        \Phake::verifyNoInteraction($this->urlMatcher);
    }

    private function createMockEndpointRepository(): EndpointRepository
    {
        return new EndpointRepository($this->specificationLoader, self::SPECIFICATION_URL);
    }

    private function givenEndpointCollection(): EndpointCollection
    {
        $parameters = new Endpoint();
        $parameters->path = self::PATH;
        $parameters->httpMethod = new HttpMethodEnum(strtolower(self::HTTP_METHOD));
        $parameters->urlMatcher = $this->urlMatcher;

        return new EndpointCollection([$parameters]);
    }

    private function assertUrlMatcher_urlIsMatching_wasCalledOnceWithUrl(string $url): void
    {
        \Phake::verify($this->urlMatcher)
            ->urlIsMatching($url);
    }

    private function givenUrlMatcher_urlIsMatching_returnTrue(): void
    {
        \Phake::when($this->urlMatcher)
            ->urlIsMatching(\Phake::anyParameters())
            ->thenReturn(true);
    }

    private function givenUrlMatcher_urlIsMatching_returnFalse(): void
    {
        \Phake::when($this->urlMatcher)
            ->urlIsMatching(\Phake::anyParameters())
            ->thenReturn(false);
    }
}
