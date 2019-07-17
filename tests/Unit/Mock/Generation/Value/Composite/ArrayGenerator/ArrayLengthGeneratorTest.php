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

use App\Mock\Generation\Value\Composite\ArrayGenerator\ArrayLengthGenerator;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayLengthGeneratorTest extends TestCase
{
    private const DEFAULT_MIN_ITEMS = 1;
    private const DEFAULT_MAX_ITEMS = 20;
    private const FIXED_LENGTH = 25;

    /** @test */
    public function generateArrayLength_minAndMaxItemsAreEmpty_arrayLengthGeneratedInDefaultRange(): void
    {
        $type = new ArrayType();
        $generator = new ArrayLengthGenerator();

        $length = $generator->generateArrayLength($type);

        $this->assertGreaterThanOrEqual(self::DEFAULT_MIN_ITEMS, $length->value);
        $this->assertLessThanOrEqual(self::DEFAULT_MAX_ITEMS, $length->value);
        $this->assertSame(self::DEFAULT_MIN_ITEMS, $length->minValue);
    }

    /** @test */
    public function generateArrayLength_fixedMinAndMaxItems_fixedArrayLengthGenerated(): void
    {
        $type = new ArrayType();
        $type->minItems = self::FIXED_LENGTH;
        $type->maxItems = self::FIXED_LENGTH;
        $generator = new ArrayLengthGenerator();

        $length = $generator->generateArrayLength($type);

        $this->assertSame(self::FIXED_LENGTH, $length->value);
        $this->assertSame(self::FIXED_LENGTH, $length->minValue);
    }
}
