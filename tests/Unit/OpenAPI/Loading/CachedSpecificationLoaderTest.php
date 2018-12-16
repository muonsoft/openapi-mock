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

use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockParametersCollection;
use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\Composite\FreeFormObjectType;
use App\Mock\Parameters\Schema\Type\Composite\HashMapType;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\Primitive\BooleanType;
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\OpenAPI\Loading\CachedSpecificationLoader;
use App\Tests\Utility\TestCase\SpecificationLoaderTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class CachedSpecificationLoaderTest extends TestCase
{
    use SpecificationLoaderTestCaseTrait;

    private const OPENAPI_FILE = 'openapi_file';

    /** @var CacheInterface */
    private $cache;

    protected function setUp(): void
    {
        $this->setUpSpecificationLoader();
        $this->cache = \Phake::mock(CacheInterface::class);
    }

    /** @test */
    public function loadMockParameters_cacheItemExists_mockParametersLoadedFromCacheAndReturned(): void
    {
        $cachedSpecificationLoader = $this->createCachedSpecificationLoader();
        $cachedSpecification = $this->givenMockParametersCollection();
        $cacheKey = md5(self::OPENAPI_FILE);
        $this->givenCache_has_returns(true);
        $this->givenCache_get_returnsSerializedObject($cachedSpecification);

        $specification = $cachedSpecificationLoader->loadMockParameters(self::OPENAPI_FILE);

        $this->assertNotNull($specification);
        $this->assertCache_has_wasCalledOnceWithKey($cacheKey);
        $this->assertCache_get_wasCalledOnceWithKey($cacheKey);
        $this->assertEquals($cachedSpecification, $specification);
    }

    /** @test */
    public function loadMockParameters_cacheItemNotExists_mockParametersLoadedByLoaderAndSavedToCacheAndReturned(): void
    {
        $cachedSpecificationLoader = $this->createCachedSpecificationLoader();
        $cacheKey = md5(self::OPENAPI_FILE);
        $this->givenCache_has_returns(false);
        $loadedSpecification = $this->givenMockParametersCollection();
        $this->givenSpecificationLoader_loadMockParameters_returnsMockParametersCollection($loadedSpecification);

        $specification = $cachedSpecificationLoader->loadMockParameters(self::OPENAPI_FILE);

        $this->assertNotNull($specification);
        $this->assertCache_has_wasCalledOnceWithKey($cacheKey);
        $this->assertCache_get_wasNeverCalledWithAnyParameters();
        $this->assertSpecificationLoader_loadMockParameters_wasCalledOnceWithUrl(self::OPENAPI_FILE);
        $this->assertCache_set_wasCalledOnceWithKeyAndSerializedObject($cacheKey, $loadedSpecification);
        $this->assertSame($loadedSpecification, $specification);
    }

    /** @test */
    public function resetCache_url_cacheCleared(): void
    {
        $cachedSpecificationLoader = $this->createCachedSpecificationLoader();
        $cacheKey = md5(self::OPENAPI_FILE);

        $cachedSpecificationLoader->resetCache(self::OPENAPI_FILE);

        $this->assertCache_delete_wasCalledOnceWithKey($cacheKey);
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

    private function givenMockParametersCollection(): MockParametersCollection
    {
        $cachedSpecification = new MockParametersCollection();
        $objectType = new ObjectType();
        $objectType->properties->add(new BooleanType());
        $objectType->properties->add(new IntegerType());
        $objectType->properties->add(new NumberType());
        $objectType->properties->add(new StringType());
        $objectType->properties->add(new ArrayType());
        $objectType->properties->add(new FreeFormObjectType());
        $objectType->properties->add(new HashMapType());
        $schema = new Schema();
        $schema->value = $objectType;
        $mockResponse = new MockResponse();
        $mockResponse->content->set('application/json', $schema);
        $mockParameters = new MockParameters();
        $mockParameters->responses->set(200, $mockResponse);
        $cachedSpecification->add($mockParameters);

        return $cachedSpecification;
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
            $this->cache
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
}
