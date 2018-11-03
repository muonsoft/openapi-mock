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

use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\Type\Primitive\NumberTypeParser;
use PHPUnit\Framework\TestCase;

class NumberTypeParserTest extends TestCase
{
    /** @test */
    public function parse_validNumberSchema_numberTypeReturned(): void
    {
        $parser = new NumberTypeParser();

        $type = $parser->parse([], new ParsingContext());

        $this->assertInstanceOf(NumberType::class, $type);
    }
}
