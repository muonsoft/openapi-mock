<?php
/*
 * This file is part of swagger-mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Integration;

use App\Utility\UriLoader;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class UriLoaderTest extends TestCase
{
    private const REMOTE_FILE_NAME = 'http://example.com';

    /** @test */
    public function loadFileContents_remoteFileName_fileContentsLoadedAndReturned(): void
    {
        $loader = $this->createUriLoader();

        $contents = $loader->loadFileContents(self::REMOTE_FILE_NAME);

        $this->assertGreaterThan(0, \strlen($contents));
    }

    /** @test */
    public function getTimestamp_remoteFileName_timestampReturned(): void
    {
        $loader = $this->createUriLoader();

        $timestamp = $loader->getTimestamp(self::REMOTE_FILE_NAME);

        $timestamp1 = $timestamp->getTimestamp();
        $this->assertGreaterThan(0, $timestamp1);
    }

    private function createUriLoader(): UriLoader
    {
        return new UriLoader(new Client());
    }
}