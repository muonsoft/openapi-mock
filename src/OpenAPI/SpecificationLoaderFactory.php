<?php
/*
 * This file is part of swagger-mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI;

use App\Cache\CacheKeyGeneratorInterface;
use App\Cache\MD5AndTimestampKeyGenerator;
use App\Cache\MD5KeyGenerator;
use App\OpenAPI\Loading\CachedSpecificationLoader;
use App\OpenAPI\Loading\SpecificationFileLoader;
use App\OpenAPI\Parsing\SpecificationParser;
use App\Utility\UriLoader;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SpecificationLoaderFactory
{
    private const CACHE_STRATEGIES = [
        'DISABLED',
        'MD5',
        'MD5_AND_TIMESTAMP',
    ];
    private const KEY_PREFIX = 'specification_';

    /** @var UriLoader */
    private $uriLoader;

    /** @var DecoderInterface */
    private $decoder;

    /** @var SpecificationParser */
    private $parser;

    /** @var CacheInterface */
    private $cache;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        UriLoader $uriLoader,
        DecoderInterface $decoder,
        SpecificationParser $parser,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->uriLoader = $uriLoader;
        $this->decoder = $decoder;
        $this->parser = $parser;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function createSpecificationLoader(string $cacheStrategy): SpecificationLoaderInterface
    {
        $this->validateCacheStrategy($cacheStrategy);

        $loader = new SpecificationFileLoader($this->uriLoader, $this->decoder, $this->parser, $this->logger);

        if ($cacheStrategy !== 'DISABLED') {
            $generator = $this->createCacheKeyGenerator($cacheStrategy);
            $loader = new CachedSpecificationLoader($loader, $generator, $this->cache, $this->logger);
        }

        $this->logger->info(sprintf('Specification loader with caching strategy "%s" was created.', $cacheStrategy));

        return $loader;
    }

    private function validateCacheStrategy(string $cacheStrategy): void
    {
        if (!\in_array($cacheStrategy, self::CACHE_STRATEGIES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cache strategy "%s" is not valid. Use one of these values: %s.',
                    $cacheStrategy,
                    implode(', ', self::CACHE_STRATEGIES)
                )
            );
        }
    }

    private function createCacheKeyGenerator(string $cacheStrategy): CacheKeyGeneratorInterface
    {
        if ($cacheStrategy === 'MD5') {
            $generator = new MD5KeyGenerator(self::KEY_PREFIX);
        } else {
            $generator = new MD5AndTimestampKeyGenerator($this->uriLoader, self::KEY_PREFIX);
        }

        return $generator;
    }
}
