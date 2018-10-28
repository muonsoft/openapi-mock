<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Composite;

use App\Mock\Generation\Value\Composite\ArrayValueGenerator;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

class ArrayValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateValue_arrayTypeWithItemsType_arrayOfTypedItemsGeneratedAndReturned(): void
    {
        $type = new ArrayType();
        $type->items = new DummyType();
        $generator = new ArrayValueGenerator($this->valueGeneratorLocator);
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $value = $this->givenValueGenerator_generateValue_returnsValue();

        $array = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_isCalledOnceWithType($type->items);
        $this->assertValueGenerator_generateValue_isCalledAtLeastOnceWithType($type->items);
        $this->assertContains($value, $array);
    }
}
