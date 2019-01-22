<?php
/*
 * This file is part of swagger-mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI;

use App\OpenAPI\Loading\CachedSpecificationLoader;
use App\OpenAPI\Loading\SpecificationFileLoader;
use App\OpenAPI\Parsing\SpecificationParser;
use App\OpenAPI\SpecificationLoaderFactory;
use App\Utility\UriLoader;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class SpecificationLoaderFactoryTest extends TestCase
{
    /** @var UriLoader */
    private $uriLoader;

    /** @var DecoderInterface */
    private $decoder;

    /** @var SpecificationParser */
    private $parser;

    /** @var CacheInterface */
    private $cache;

    protected function setUp(): void
    {
        $this->uriLoader = \Phake::mock(UriLoader::class);
        $this->decoder = \Phake::mock(DecoderInterface::class);
        $this->parser = \Phake::mock(SpecificationParser::class);
        $this->cache = \Phake::mock(CacheInterface::class);
    }

    /**
     * @test
     * @dataProvider cacheStrategyAndSpecificationLoaderClass
     */
    public function createSpecificationLoader_validCacheStrategy_specificationLoaderOfExpectedTypeReturned(
        string $cacheStrategy,
        string $loaderClass
    ): void {
        $factory = $this->createSpecificationLoaderFactory();

        $loader = $factory->createSpecificationLoader($cacheStrategy);

        $this->assertInstanceOf($loaderClass, $loader);
    }

    public function cacheStrategyAndSpecificationLoaderClass(): array
    {
        return [
            ['DISABLED', SpecificationFileLoader::class],
            ['MD5', CachedSpecificationLoader::class],
            ['MD5_AND_TIMESTAMP', CachedSpecificationLoader::class],
        ];
    }

    /** @test */
    public function createSpecificationLoader_invalidCacheStrategy_exceptionThrown(): void
    {
        $factory = $this->createSpecificationLoaderFactory();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache strategy "invalid" is not valid. Use one of these values:');

        $factory->createSpecificationLoader('invalid');
    }

    private function createSpecificationLoaderFactory(): SpecificationLoaderFactory
    {
        return new SpecificationLoaderFactory($this->uriLoader, $this->decoder, $this->parser, $this->cache);
    }
}
