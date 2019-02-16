<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Loading;

use App\Cache\CacheKeyGeneratorInterface;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointCollection;
use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\MockResponseCollection;
use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\SchemaCollection;
use App\Mock\Parameters\Schema\Type\Combined\AllOfType;
use App\Mock\Parameters\Schema\Type\Combined\AnyOfType;
use App\Mock\Parameters\Schema\Type\Combined\OneOfType;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\Composite\FreeFormObjectType;
use App\Mock\Parameters\Schema\Type\Composite\HashMapType;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\InvalidType;
use App\Mock\Parameters\Schema\Type\Primitive\BooleanType;
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\Mock\Parameters\Schema\Type\TypeCollection;
use App\OpenAPI\Loading\CachedSpecificationLoader;
use App\OpenAPI\Routing\NullUrlMatcher;
use App\OpenAPI\Routing\RegularExpressionUrlMatcher;
use App\Tests\Utility\TestCase\SpecificationLoaderTestCaseTrait;
use App\Utility\StringList;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

class CachedSpecificationLoaderTest extends TestCase
{
    use SpecificationLoaderTestCaseTrait;

    private const EXPECTED_ALLOWED_CLASSES = [
        StringList::class,
        EndpointCollection::class,
        Endpoint::class,
        EndpointParameter::class,
        EndpointParameterCollection::class,
        NullUrlMatcher::class,
        RegularExpressionUrlMatcher::class,
        MockResponseCollection::class,
        MockResponse::class,
        SchemaCollection::class,
        Schema::class,
        TypeCollection::class,
        BooleanType::class,
        IntegerType::class,
        NumberType::class,
        StringType::class,
        ArrayType::class,
        ObjectType::class,
        FreeFormObjectType::class,
        HashMapType::class,
        OneOfType::class,
        AnyOfType::class,
        AllOfType::class,
        InvalidType::class,
    ];

    private const OPENAPI_FILE = 'openapi_file';

    /** @var CacheKeyGeneratorInterface */
    private $cacheKeyGenerator;

    /** @var CacheInterface */
    private $cache;

    protected function setUp(): void
    {
        $this->setUpSpecificationLoader();
        $this->cacheKeyGenerator = \Phake::mock(CacheKeyGeneratorInterface::class);
        $this->cache = \Phake::mock(CacheInterface::class);
    }

    /** @test */
    public function loadMockEndpoints_cacheItemExists_mockEndpointLoadedFromCacheAndReturned(): void
    {
        $cachedSpecificationLoader = $this->createCachedSpecificationLoader();
        $cachedSpecification = $this->givenMockEndpointCollection();
        $cacheKey = $this->givenCacheKeyGenerator_generateKey_returnsCacheKey();
        $this->givenCache_has_returns(true);
        $this->givenCache_get_returnsSerializedObject($cachedSpecification);

        $specification = $cachedSpecificationLoader->loadMockEndpoints(self::OPENAPI_FILE);

        $this->assertNotNull($specification);
        $this->assertCacheKeyGenerator_generateKey_wasCalledOnceWithUrl(self::OPENAPI_FILE);
        $this->assertCache_has_wasCalledOnceWithKey($cacheKey);
        $this->assertCache_get_wasCalledOnceWithKey($cacheKey);
    }

    /** @test */
    public function loadMockEndpoints_cacheItemNotExists_mockEndpointLoadedByLoaderAndSavedToCacheAndReturned(): void
    {
        $cachedSpecificationLoader = $this->createCachedSpecificationLoader();
        $cacheKey = $this->givenCacheKeyGenerator_generateKey_returnsCacheKey();
        $this->givenCache_has_returns(false);
        $loadedSpecification = $this->givenMockEndpointCollection();
        $this->givenSpecificationLoader_loadMockEndpoints_returnsMockEndpointCollection($loadedSpecification);

        $specification = $cachedSpecificationLoader->loadMockEndpoints(self::OPENAPI_FILE);

        $this->assertNotNull($specification);
        $this->assertCacheKeyGenerator_generateKey_wasCalledOnceWithUrl(self::OPENAPI_FILE);
        $this->assertCache_has_wasCalledOnceWithKey($cacheKey);
        $this->assertCache_get_wasNeverCalledWithAnyParameters();
        $this->assertSpecificationLoader_loadMockEndpoints_wasCalledOnceWithUrl(self::OPENAPI_FILE);
        $this->assertCache_set_wasCalledOnceWithKeyAndSerializedObject($cacheKey, $loadedSpecification);
        $this->assertSame($loadedSpecification, $specification);
    }

    /** @test */
    public function resetCache_url_cacheCleared(): void
    {
        $cachedSpecificationLoader = $this->createCachedSpecificationLoader();
        $cacheKey = $this->givenCacheKeyGenerator_generateKey_returnsCacheKey();

        $cachedSpecificationLoader->resetCache(self::OPENAPI_FILE);

        $this->assertCacheKeyGenerator_generateKey_wasCalledOnceWithUrl(self::OPENAPI_FILE);
        $this->assertCache_delete_wasCalledOnceWithKey($cacheKey);
    }

    /** @test */
    public function loaderHasAllExpectedClassesForUnserialization(): void
    {
        foreach (self::EXPECTED_ALLOWED_CLASSES as $allowedClass) {
            $this->assertContains($allowedClass, CachedSpecificationLoader::ALLOWED_CLASSES);
        }
    }

    private function assertCache_has_wasCalledOnceWithKey(string $cacheKey): void
    {
        \Phake::verify($this->cache)
            ->has($cacheKey);
    }

    private function assertCache_get_wasCalledOnceWithKey(string $cacheKey): void
    {
        \Phake::verify($this->cache)
            ->get($cacheKey);
    }

    private function assertCache_get_wasNeverCalledWithAnyParameters(): void
    {
        \Phake::verify($this->cache, \Phake::times(0))
            ->get(\Phake::anyParameters());
    }

    private function givenCache_has_returns(bool $cacheItemExists): void
    {
        \Phake::when($this->cache)
            ->has(\Phake::anyParameters())
            ->thenReturn($cacheItemExists);
    }

    private function givenMockEndpointCollection(): EndpointCollection
    {
        $endpoints = new EndpointCollection();

        $objectType = new ObjectType();
        $objectType->properties->add(new BooleanType());
        $objectType->properties->add(new IntegerType());
        $objectType->properties->add(new NumberType());
        $objectType->properties->add(new StringType());
        $objectType->properties->add(new ArrayType());
        $objectType->properties->add(new FreeFormObjectType());
        $objectType->properties->add(new HashMapType());
        $objectType->properties->add(new OneOfType());
        $objectType->properties->add(new AnyOfType());
        $objectType->properties->add(new AllOfType());
        $objectType->properties->add(new InvalidType(''));
        $schema = new Schema();
        $schema->value = $objectType;
        $mockResponse = new MockResponse();
        $mockResponse->content->set('application/json', $schema);
        $endpoint = new Endpoint();
        $parameter = new EndpointParameter();
        $endpoint->parameters->add($parameter);
        $endpoint->responses->set(200, $mockResponse);
        $endpoints->add($endpoint);

        return $endpoints;
    }

    private function givenCache_get_returnsSerializedObject(object $object): void
    {
        $serializedValue = serialize($object);
        \Phake::when($this->cache)
            ->get(\Phake::anyParameters())
            ->thenReturn($serializedValue);
    }

    private function createCachedSpecificationLoader(): CachedSpecificationLoader
    {
        return new CachedSpecificationLoader(
            $this->specificationLoader,
            $this->cacheKeyGenerator,
            $this->cache,
            new NullLogger()
        );
    }

    private function assertCache_set_wasCalledOnceWithKeyAndSerializedObject(string $cacheKey, object $object): void
    {
        \Phake::verify($this->cache)
            ->set($cacheKey, serialize($object));
    }

    private function assertCache_delete_wasCalledOnceWithKey($cacheKey): void
    {
        \Phake::verify($this->cache)
            ->delete($cacheKey);
    }

    private function assertCacheKeyGenerator_generateKey_wasCalledOnceWithUrl(string $url): void
    {
        \Phake::verify($this->cacheKeyGenerator)
            ->generateKey($url);
    }

    private function givenCacheKeyGenerator_generateKey_returnsCacheKey(): string
    {
        $cacheKey = 'cache_key';
        \Phake::when($this->cacheKeyGenerator)
            ->generateKey(\Phake::anyParameters())
            ->thenReturn($cacheKey);

        return $cacheKey;
    }
}
