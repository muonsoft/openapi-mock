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

use App\OpenAPI\Parsing\ParsingContext;
use PHPUnit\Framework\TestCase;

class ParsingContextTest extends TestCase
{
    private const PATH = 'path';
    private const SUB_PATH = 'sub_path';
    private const FULL_PATH = self::PATH . '.' . self::SUB_PATH;

    /** @test */
    public function getPath_noPathIsSet_emptyPathReturned(): void
    {
        $context = new ParsingContext();

        $this->assertSame('', $context->getPath());
    }

    /** @test */
    public function addSubPath_noPathIsSetAndGivenSubPath_pathReturned(): void
    {
        $context = new ParsingContext();

        $context->addSubPath(self::PATH);

        $this->assertSame(self::PATH, $context->getPath());
    }

    /** @test */
    public function withSubPath_contextWithEmptyPathAndSubPathGiven_newContextWithFullPathCreatedAndReturned(): void
    {
        $context = new ParsingContext();

        $newContext = $context->withSubPath(self::SUB_PATH);

        $this->assertNotNull($newContext);
        $this->assertNotSame($newContext, $context);
        $this->assertSame(self::SUB_PATH, $newContext->getPath());
    }

    /** @test */
    public function withSubPath_contextWithPathAndSubPathGiven_newContextWithFullPathCreatedAndReturned(): void
    {
        $context = new ParsingContext();
        $context->addSubPath(self::PATH);

        $newContext = $context->withSubPath(self::SUB_PATH);

        $this->assertNotNull($newContext);
        $this->assertNotSame($newContext, $context);
        $this->assertSame(self::FULL_PATH, $newContext->getPath());
    }
}
