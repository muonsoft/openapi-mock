<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Routing;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class NullUrlMatcher implements UrlMatcherInterface
{
    public function urlIsMatching(string $url): bool
    {
        return false;
    }
}
