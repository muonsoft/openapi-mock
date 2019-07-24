<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Length;

use App\Mock\Generation\Value\Length\LengthGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class LengthGeneratorTest extends TestCase
{
    private const DEFAULT_MAX_ITEMS = 20;
    private const FIXED_LENGTH = 25;

    /** @test */
    public function generateArrayLength_minAndMaxItemsAreEmpty_arrayLengthGeneratedInDefaultRange(): void
    {
        $generator = new LengthGenerator();

        $length = $generator->generateLength(0, 0);

        $this->assertGreaterThanOrEqual(0, $length->value);
        $this->assertLessThanOrEqual(self::DEFAULT_MAX_ITEMS, $length->value);
        $this->assertSame(0, $length->minValue);
    }

    /** @test */
    public function generateArrayLength_maxIsLessThanMin_fixedLengthGenerated(): void
    {
        $generator = new LengthGenerator();

        $length = $generator->generateLength(self::FIXED_LENGTH, 0);

        $this->assertSame(self::FIXED_LENGTH, $length->value);
        $this->assertSame(self::FIXED_LENGTH, $length->minValue);
    }

    /** @test */
    public function generateArrayLength_fixedMinAndMaxItems_fixedArrayLengthGenerated(): void
    {
        $generator = new LengthGenerator();

        $length = $generator->generateLength(self::FIXED_LENGTH, self::FIXED_LENGTH);

        $this->assertSame(self::FIXED_LENGTH, $length->value);
        $this->assertSame(self::FIXED_LENGTH, $length->minValue);
    }
}
