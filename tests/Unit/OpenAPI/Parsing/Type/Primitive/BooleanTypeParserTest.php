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
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\Primitive\BooleanTypeParser;
use PHPUnit\Framework\TestCase;

class BooleanTypeParserTest extends TestCase
{
    /** @test */
    public function parsePointedSchema_validBooleanSchema_booleanTypeReturned(): void
    {
        $parser = new BooleanTypeParser();

        /** @var BooleanType $type */
        $type = $parser->parsePointedSchema(new SpecificationAccessor([]), new SpecificationPointer());

        $this->assertInstanceOf(BooleanType::class, $type);
        $this->assertFalse($type->nullable);
    }

    /** @test */
    public function parsePointedSchema_fixedFieldsSchema_typeWithValidFixedFieldsReturned(): void
    {
        $parser = new BooleanTypeParser();
        $specification = new SpecificationAccessor([
            'nullable'  => true,
            'readOnly'  => true,
            'writeOnly' => true,
        ]);

        /** @var TypeInterface $type */
        $type = $parser->parsePointedSchema($specification, new SpecificationPointer());

        $this->assertTrue($type->isNullable());
        $this->assertTrue($type->isReadOnly());
        $this->assertTrue($type->isWriteOnly());
    }
}
