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

use App\Mock\Parameters\Schema\Type\Primitive\BooleanType;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Primitive\BooleanTypeParser;
use PHPUnit\Framework\TestCase;

class BooleanTypeParserTest extends TestCase
{
    /** @test */
    public function parse_validBooleanSchema_booleanTypeReturned(): void
    {
        $parser = new BooleanTypeParser();

        $type = $parser->parse([], new SpecificationPointer());

        $this->assertInstanceOf(BooleanType::class, $type);
    }
}
