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

use App\Mock\Generation\Value\Primitive\RandomIntegerGenerator;
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class RandomIntegerGeneratorTest extends TestCase
{
    use ProbabilityTestCaseTrait;

    private const MINIMUM = 5;
    private const MAXIMUM = 10;
    private const MULTIPLE_OF = 10;

    /** @test */
    public function generateValue_integerTypeWithLimits_limitedIntegerValueReturned(): void
    {
        $generator = $this->createFakerIntegerGenerator();
        $type = new IntegerType();
        $type->minimum = self::MINIMUM;
        $type->maximum = self::MAXIMUM;
        $type->exclusiveMinimum = true;
        $type->exclusiveMaximum = true;

        $value = $generator->generateValue($type);

        $this->assertGreaterThan(self::MINIMUM, $value);
        $this->assertLessThan(self::MAXIMUM, $value);
    }

    /** @test */
    public function generateValue_integerTypeWithMultipleOfParameter_multipleOfParameterIntegerValueReturned(): void
    {
        $generator = $this->createFakerIntegerGenerator();
        $type = new IntegerType();
        $type->minimum = self::MULTIPLE_OF;
        $type->maximum = 100;
        $type->multipleOf = self::MULTIPLE_OF;

        $value = $generator->generateValue($type);
        $valueMod = $value % self::MULTIPLE_OF;

        $this->assertSame(0, $valueMod);
    }

    /** @test */
    public function generateValue_integerTypeWithNullableParameters_nullValueReturned(): void
    {
        $generator = $this->createFakerIntegerGenerator();
        $type = new IntegerType();
        $type->nullable = true;

        $test = function () use ($generator, $type) {
            return $generator->generateValue($type);
        };

        $this->expectClosureOccasionallyReturnsNull($test);
    }

    private function createFakerIntegerGenerator(): RandomIntegerGenerator
    {
        return new RandomIntegerGenerator();
    }
}
