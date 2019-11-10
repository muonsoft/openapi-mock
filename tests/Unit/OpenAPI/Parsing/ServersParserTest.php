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

use App\Mock\Parameters\Servers;
use App\OpenAPI\Parsing\ServersParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ServersParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    /** @test */
    public function parsePointedSchema_validUrls_urlsReturned(): void
    {
        $parser = new ServersParser($this->errorHandler);
        $specification = new SpecificationAccessor([
            [
                'url' => 'http://example.com',
            ],
            [
                'url' => 'http://example.com/base/path',
            ],
        ]);

        /** @var Servers $servers */
        $servers = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertInstanceOf(Servers::class, $servers);
        $this->assertSame(
            [
                'http://example.com',
                'http://example.com/base/path',
            ],
            $servers->urls->toArray()
        );
    }

    /** @test */
    public function parsePointedSchema_invalidServerSchema_emptyListAndErrorReported(): void
    {
        $parser = new ServersParser($this->errorHandler);
        $pointer = new SpecificationPointer();
        $specification = new SpecificationAccessor([
            ['invalid'],
        ]);

        /** @var Servers $servers */
        $servers = $parser->parsePointedSchema($specification, $pointer);

        $this->assertInstanceOf(Servers::class, $servers);
        $this->assertCount(0, $servers->urls);
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(
            'Server schema is invalid and will be ignored',
            ['0']
        );
    }
}
