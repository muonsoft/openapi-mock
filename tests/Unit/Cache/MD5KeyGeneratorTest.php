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

use App\Cache\MD5KeyGenerator;
use PHPUnit\Framework\TestCase;

class MD5KeyGeneratorTest extends TestCase
{
    private const URL = 'url';

    /** @test */
    public function generateKey_url_md5hashReturned(): void
    {
        $keyFactory = new MD5KeyGenerator();

        $key = $keyFactory->generateKey(self::URL);

        $this->assertSame('572d4e421e5e6b9bc11d815e8a027112', $key);
    }

    /** @test */
    public function generateKey_urlAndKeyPrefix_prefixedMd5hashReturned(): void
    {
        $keyFactory = new MD5KeyGenerator('prefix_');

        $key = $keyFactory->generateKey(self::URL);

        $this->assertSame('prefix_572d4e421e5e6b9bc11d815e8a027112', $key);
    }
}
