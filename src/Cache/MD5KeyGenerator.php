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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MD5KeyGenerator implements CacheKeyGeneratorInterface
{
    /** @var string */
    private $keyPrefix;

    public function __construct(string $keyPrefix = '')
    {
        $this->keyPrefix = $keyPrefix;
    }

    public function generateKey(string $url): string
    {
        return $this->keyPrefix . md5($url);
    }
}
