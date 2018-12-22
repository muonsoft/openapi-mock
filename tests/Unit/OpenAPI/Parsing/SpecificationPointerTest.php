<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing;

use App\OpenAPI\Parsing\SpecificationPointer;
use PHPUnit\Framework\TestCase;

class SpecificationPointerTest extends TestCase
{
    private const PATH = 'path';
    private const SUB_PATH = 'sub_path';
    private const FULL_PATH = self::PATH . '.' . self::SUB_PATH;

    /** @test */
    public function getPathElements_noPathIsSet_emptyPathReturned(): void
    {
        $pointer = new SpecificationPointer();

        $elements = $pointer->getPathElements();

        $this->assertSame([], $elements);
    }

    /** @test */
    public function getPath_noPathIsSet_emptyPathReturned(): void
    {
        $pointer = new SpecificationPointer();

        $this->assertSame('', $pointer->getPath());
    }

    /** @test */
    public function addSubPath_noPathIsSetAndGivenSubPath_pathReturned(): void
    {
        $pointer = new SpecificationPointer();

        $pointer->addSubPath(self::PATH);

        $this->assertSame(self::PATH, $pointer->getPath());
        $this->assertSame([self::PATH], $pointer->getPathElements());
    }

    /** @test */
    public function withSubPath_contextWithEmptyPathAndSubPathGiven_newContextWithFullPathCreatedAndReturned(): void
    {
        $pointer = new SpecificationPointer();

        $newPointer = $pointer->withSubPath(self::SUB_PATH);

        $this->assertNotNull($newPointer);
        $this->assertNotSame($newPointer, $pointer);
        $this->assertSame(self::SUB_PATH, $newPointer->getPath());
        $this->assertSame([self::SUB_PATH], $newPointer->getPathElements());
    }

    /** @test */
    public function withSubPath_contextWithPathAndSubPathGiven_newContextWithFullPathCreatedAndReturned(): void
    {
        $pointer = new SpecificationPointer();
        $pointer->addSubPath(self::PATH);

        $newPointer = $pointer->withSubPath(self::SUB_PATH);

        $this->assertNotNull($newPointer);
        $this->assertNotSame($newPointer, $pointer);
        $this->assertSame(self::FULL_PATH, $newPointer->getPath());
        $this->assertSame([self::PATH, self::SUB_PATH], $newPointer->getPathElements());
    }
}
