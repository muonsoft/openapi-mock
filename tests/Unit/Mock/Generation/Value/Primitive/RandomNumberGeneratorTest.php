<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Primitive;

use App\Mock\Generation\Value\Primitive\RandomNumberGenerator;
use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class RandomNumberGeneratorTest extends TestCase
{
    use ProbabilityTestCaseTrait;

    private const MINIMUM = 10;
    private const MAXIMUM = 11;
    private const MULTIPLE_OF = 2.5;
    private const VALUE_DEVIATION = 0.001;

    /** @test */
    public function generateValue_numberType_randomNumberValueReturned(): void
    {
        $generator = $this->createRandomNumberGenerator();
        $type = new NumberType();

        $test = function () use ($generator, $type) {
            $value = $generator->generateValue($type);

            return abs($value) - floor(abs($value));
        };

        $this->expectClosureOccasionallyReturnsValueGreaterThan($test, self::VALUE_DEVIATION);
    }

    /** @test */
    public function generateValue_numberTypeWithLimits_limitedNumberValueReturned(): void
    {
        $generator = $this->createRandomNumberGenerator();
        $type = new NumberType();
        $type->minimum = self::MINIMUM;
        $type->maximum = self::MAXIMUM;
        $type->exclusiveMinimum = true;
        $type->exclusiveMaximum = true;

        $value = $generator->generateValue($type);

        $this->assertGreaterThan(self::MINIMUM, $value);
        $this->assertLessThan(self::MAXIMUM, $value);
    }

    /** @test */
    public function generateValue_numberTypeWithMultipleOfParameter_multipleOfParameterNumberValueReturned(): void
    {
        $generator = $this->createRandomNumberGenerator();
        $type = new NumberType();
        $type->minimum = self::MULTIPLE_OF;
        $type->maximum = 100;
        $type->multipleOf = self::MULTIPLE_OF;

        $value = $generator->generateValue($type);
        $roundedValue = floor($value / self::MULTIPLE_OF) * self::MULTIPLE_OF;
        $delta = abs($value - $roundedValue);

        $this->assertLessThan(self::VALUE_DEVIATION, $delta);
    }

    /** @test */
    public function generateValue_numberTypeWithNullableParameters_nullValueReturned(): void
    {
        $generator = $this->createRandomNumberGenerator();
        $type = new NumberType();
        $type->nullable = true;

        $test = function () use ($generator, $type) {
            return $generator->generateValue($type);
        };

        $this->expectClosureOccasionallyReturnsNull($test);
    }

    private function createRandomNumberGenerator(): RandomNumberGenerator
    {
        return new RandomNumberGenerator();
    }
}
