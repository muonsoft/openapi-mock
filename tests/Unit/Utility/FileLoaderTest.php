<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Utility;

use App\Utility\FileLoader;
use PHPUnit\Framework\TestCase;

class FileLoaderTest extends TestCase
{
    private const DUMMY_JSON_FILE = __DIR__ . '/../../Resources/dummy.json';

    /** @test */
    public function loadFileContents_validFileName_contentsLoadedAndReturned(): void
    {
        $loader = new FileLoader();

        $contents = $loader->loadFileContents(self::DUMMY_JSON_FILE);

        $this->assertStringEqualsFile(self::DUMMY_JSON_FILE, $contents);
    }
}
