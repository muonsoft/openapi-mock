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

use App\Mock\Parameters\Servers;
use App\OpenAPI\Routing\ServerPathMaker;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ServerPathMakerTest extends TestCase
{
    /** @test */
    public function createServerPaths_emptyUrls_emptyPaths(): void
    {
        $servers = new Servers();
        $maker = new ServerPathMaker();

        $paths = $maker->createServerPaths($servers);

        $this->assertSame([], $paths);
    }

    /** @test */
    public function createServerPaths_absoluteAndRelativeUrls_onlyRelativePaths(): void
    {
        $servers = new Servers();
        $servers->urls->add('http://example.com/absolute/path  ');
        $servers->urls->add('/relative/path');
        $servers->urls->add('relative/path');
        $maker = new ServerPathMaker();

        $paths = $maker->createServerPaths($servers);

        $this->assertSame(['/absolute/path', '/relative/path'], $paths);
    }
}
