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

use App\Cache\CacheKeyGeneratorInterface;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\EndpointCollection;
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
use App\OpenAPI\SpecificationLoaderInterface;
use App\Utility\StringList;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class CachedSpecificationLoader implements SpecificationLoaderInterface
{
    /** @var SpecificationLoaderInterface */
    private $specificationLoader;

    /** @var CacheKeyGeneratorInterface */
    private $cacheKeyGenerator;

    /** @var CacheInterface */
    private $cache;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        SpecificationLoaderInterface $specificationLoader,
        CacheKeyGeneratorInterface $cacheKeyGenerator,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->specificationLoader = $specificationLoader;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function loadMockEndpoints(string $url): EndpointCollection
    {
        $cacheKey = $this->cacheKeyGenerator->generateKey($url);

        if ($this->cache->has($cacheKey)) {
            $specification = $this->loadFromCache($cacheKey);

            $this->logger->info(sprintf('OpenAPI specification "%s" loaded from cache "%s".', $url, $cacheKey));
        } else {
            $specification = $this->specificationLoader->loadMockEndpoints($url);
            $this->cache->set($cacheKey, serialize($specification));

            $this->logger->info(sprintf('OpenAPI specification "%s" saved to cache "%s".', $url, $cacheKey));
        }

        return $specification;
    }

    public function resetCache(string $url): void
    {
        $cacheKey = $this->cacheKeyGenerator->generateKey($url);
        $this->cache->delete($cacheKey);
    }

    private function loadFromCache($cacheKey): EndpointCollection
    {
        $serializedSpecification = $this->cache->get($cacheKey);

        return unserialize(
            $serializedSpecification,
            [
                'allowed_classes' => [
                    StringList::class,
                    EndpointCollection::class,
                    Endpoint::class,
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
                ],
            ]
        );
    }
}
