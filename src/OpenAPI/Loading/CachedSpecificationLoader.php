<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Loading;

use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockParametersCollection;
use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\MockResponseCollection;
use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\SchemaCollection;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\Primitive\BooleanType;
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\Mock\Parameters\Schema\Type\TypeCollection;
use App\OpenAPI\SpecificationLoaderInterface;
use App\Utility\StringList;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class CachedSpecificationLoader implements SpecificationLoaderInterface
{
    /** @var SpecificationLoaderInterface */
    private $specificationLoader;

    /** @var CacheInterface */
    private $cache;

    public function __construct(
        SpecificationLoaderInterface $specificationLoader,
        CacheInterface $cache
    ) {
        $this->specificationLoader = $specificationLoader;
        $this->cache = $cache;
    }

    public function loadMockParameters(string $url): MockParametersCollection
    {
        $cacheKey = $this->createCacheKeyByUrl($url);

        if ($this->cache->has($cacheKey)) {
            $specification = $this->loadFromCache($cacheKey);
        } else {
            $specification = $this->specificationLoader->loadMockParameters($url);
            $this->cache->set($cacheKey, serialize($specification));
        }

        return $specification;
    }

    public function resetCache(string $url): void
    {
        $cacheKey = $this->createCacheKeyByUrl($url);
        $this->cache->delete($cacheKey);
    }

    private function createCacheKeyByUrl(string $url): string
    {
        return md5($url);
    }

    private function loadFromCache($cacheKey): MockParametersCollection
    {
        $serializedSpecification = $this->cache->get($cacheKey);

        return unserialize(
            $serializedSpecification,
            [
                'allowed_classes' => [
                    StringList::class,
                    MockParametersCollection::class,
                    MockParameters::class,
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
                ]
            ]
        );
    }
}
