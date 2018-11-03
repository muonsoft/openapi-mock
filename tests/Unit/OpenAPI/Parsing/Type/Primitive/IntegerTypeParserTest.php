<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing\Type\Primitive;

use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\Type\Primitive\IntegerTypeParser;
use PHPUnit\Framework\TestCase;

class IntegerTypeParserTest extends TestCase
{
    /** @test */
    public function parse_validIntegerSchema_integerTypeReturned(): void
    {
        $parser = new IntegerTypeParser();

        $type = $parser->parse([], new ParsingContext());

        $this->assertInstanceOf(IntegerType::class, $type);
    }
}
