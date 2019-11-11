<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Routing;

use App\OpenAPI\Routing\RegularExpressionUrlMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RegularExpressionUrlMatcherTest extends TestCase
{
    /**
     * @test
     * @dataProvider patternAndUrlAndIsMatchingProvider
     */
    public function urlIsMatching_givenUrl_isMatchingReturned(string $pattern, string $url, bool $expectedIsMatching): void
    {
        $matcher = new RegularExpressionUrlMatcher($pattern);

        $isMatching = $matcher->urlIsMatching($url);

        $this->assertSame($expectedIsMatching, $isMatching);
    }

    public function patternAndUrlAndIsMatchingProvider(): \Iterator
    {
        yield ['/^\/resource\/([^\/]*)$/', '/resource', false];
        yield ['/^\/resource\/([^\/]*)$/', '/resource/id', true];
        yield ['/^\/resource\/([^\/]*)$/', '/resource/id/subresource', false];
        yield ['/^\/resource\/([^\/]*)\/subresource$/', '/resource/id/subresource', true];
        yield ['/^\/resource\/(-?\d*)$/', '/resource/id', false];
        yield ['/^\/resource\/(-?\d*)$/', '/resource/0123456789', true];
        yield ['/^\/resource\/(-?\d*)$/', '/resource/-0123456789', true];
        yield ['/^\/resource\/(-?(?:\d+|\d*\.\d+))$/', '/resource/0123456789', true];
        yield ['/^\/resource\/(-?(?:\d+|\d*\.\d+))$/', '/resource/0123456789.0123456789', true];
        yield ['/^\/resource\/(-?(?:\d+|\d*\.\d+))$/', '/resource/-0123456789.0123456789', true];
        yield ['/^\/resource\/(-?(?:\d+|\d*\.\d+))$/', '/resource/-0123456789.a', false];
        yield ['/^(\/first\/path|\/second\/path)\/resource$/', '/resource', false];
        yield ['/^(\/first\/path|\/second\/path)\/resource$/', '/first/path/resource', true];
        yield ['/^(\/first\/path|\/second\/path)\/resource$/', '/second/path/resource', true];
    }
}
