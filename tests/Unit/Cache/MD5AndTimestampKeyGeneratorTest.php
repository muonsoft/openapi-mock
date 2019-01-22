<?php
/*
 * This file is part of swagger-mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Cache;

use App\Cache\MD5AndTimestampKeyGenerator;
use App\Utility\UriLoader;
use PHPUnit\Framework\TestCase;

class MD5AndTimestampKeyGeneratorTest extends TestCase
{
    private const URL = 'url';

    /** @var UriLoader */
    private $uriLoader;

    protected function setUp(): void
    {
        $this->uriLoader = \Phake::mock(UriLoader::class);
    }

    /** @test */
    public function generateKey_url_md5AndTimestampHashReturned(): void
    {
        $keyFactory = new MD5AndTimestampKeyGenerator($this->uriLoader);
        $dateTime = $this->givenUriLoader_getTimestamp_returnsDateTime();

        $key = $keyFactory->generateKey(self::URL);

        $this->assertSame(md5(self::URL.$dateTime->getTimestamp()), $key);
        $this->assertUriLoader_getTimestamp_wasCalledOnceWithUri(self::URL);
    }

    /** @test */
    public function generateKey_urlAndKeyPrefix_prefixedMd5AndTimestampHashReturned(): void
    {
        $keyFactory = new MD5AndTimestampKeyGenerator($this->uriLoader, 'prefix_');
        $dateTime = $this->givenUriLoader_getTimestamp_returnsDateTime();

        $key = $keyFactory->generateKey(self::URL);

        $this->assertSame('prefix_'.md5(self::URL.$dateTime->getTimestamp()), $key);
        $this->assertUriLoader_getTimestamp_wasCalledOnceWithUri(self::URL);
    }

    private function assertUriLoader_getTimestamp_wasCalledOnceWithUri(string $uri): void
    {
        \Phake::verify($this->uriLoader)
            ->getTimestamp($uri);
    }

    private function givenUriLoader_getTimestamp_returnsDateTime(): \DateTime
    {
        $dateTime = new \DateTime();

        \Phake::when($this->uriLoader)
            ->getTimestamp(\Phake::anyParameters())
            ->thenReturn($dateTime);

        return $dateTime;
    }
}
