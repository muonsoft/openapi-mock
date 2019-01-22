<?php
/*
 * This file is part of swagger-mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Cache;

use App\Utility\UriLoader;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MD5AndTimestampKeyGenerator implements CacheKeyGeneratorInterface
{
    /** @var UriLoader */
    private $uriLoader;

    /** @var string */
    private $keyPrefix;

    public function __construct(UriLoader $uriLoader, string $keyPrefix = '')
    {
        $this->uriLoader = $uriLoader;
        $this->keyPrefix = $keyPrefix;
    }

    public function generateKey(string $url): string
    {
        $timestamp = $this->uriLoader->getTimestamp($url);

        return $this->keyPrefix.md5($url.$timestamp->getTimestamp());
    }
}
