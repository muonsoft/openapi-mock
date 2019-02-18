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

use App\OpenAPI\Routing\NullUrlMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class NullUrlMatcherTest extends TestCase
{
    /** @test */
    public function urlIsMatching_anyUrl_falseReturned(): void
    {
        $matcher = new NullUrlMatcher();

        $isMatching = $matcher->urlIsMatching('');

        $this->assertFalse($isMatching);
    }
}
