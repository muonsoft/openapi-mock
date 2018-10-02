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

use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\OpenAPI\Parsing\Type\Primitive\StringTypeParser;
use PHPUnit\Framework\TestCase;

class StringTypeParserTest extends TestCase
{
    /** @test */
    public function parseTypeSchema_validStringTypeSchema_stringTypeReturned(): void
    {
        $parser = new StringTypeParser();

        $type = $parser->parseTypeSchema([]);

        $this->assertInstanceOf(StringType::class, $type);
    }
}
