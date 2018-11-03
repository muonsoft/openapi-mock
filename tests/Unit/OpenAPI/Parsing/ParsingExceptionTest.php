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
use App\OpenAPI\Parsing\ParsingException;
use PHPUnit\Framework\TestCase;

class ParsingExceptionTest extends TestCase
{
    /** @test */
    public function construct_messageAndContext_contextIsSetAndMessageHasPath(): void
    {
        $context = new ParsingContext();
        $context->addSubPath('path');

        $exception = new ParsingException('message', $context);

        $this->assertSame('Parsing error "message" at path "path".', $exception->getMessage());
    }
}
