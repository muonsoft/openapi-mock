<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Composite\ArrayGenerator;

use App\Mock\Generation\Value\Composite\ArrayGenerator\ArrayRegularValueGenerator;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayRegularValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    private const ARRAY_LENGTH = 1;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateArray_arrayTypeWithItemsTypeAndDefaultSize_arrayOfTypedItemsGeneratedAndReturned(): void
    {
        $generator = $this->createArrayRegularValueGenerator();
        $type = new ArrayType();
        $type->minItems = 1;
        $type->maxItems = 3;
        $type->items = new DummyType();
        $value = $this->givenValueGenerator_generateValue_returnsGeneratedValue();
        $this->givenLengthGeneratorGeneratesLength(self::ARRAY_LENGTH);

        $array = $generator->generateArray($this->valueGenerator, $type);

        $this->assertValueGenerator_generateValue_wasCalledAtLeastOnceWithType($type->items);
        $this->assertContains($value, $array);
        $this->assertCount(self::ARRAY_LENGTH, $array);
        $this->assertLengthGeneratedInRange(1, 3);
    }

    private function createArrayRegularValueGenerator(): ArrayRegularValueGenerator
    {
        return new ArrayRegularValueGenerator($this->lengthGenerator);
    }
}
