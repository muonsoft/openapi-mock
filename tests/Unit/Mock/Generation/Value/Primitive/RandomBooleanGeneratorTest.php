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

use App\Mock\Generation\Value\Primitive\RandomBooleanGenerator;
use App\Mock\Parameters\Schema\Type\Primitive\BooleanType;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class RandomBooleanGeneratorTest extends TestCase
{
    use ProbabilityTestCaseTrait;

    /** @test */
    public function generateValue_booleanType_randomBooleanValueReturned(): void
    {
        $generator = new RandomBooleanGenerator();
        $type = new BooleanType();

        $value = $generator->generateValue($type);

        $this->assertNotNull($value);
    }

    /** @test */
    public function generateValue_booleanTypeWithNullableParameters_nullValueReturned(): void
    {
        $generator = new RandomBooleanGenerator();
        $type = new BooleanType();
        $type->nullable = true;

        $test = function () use ($generator, $type) {
            return $generator->generateValue($type);
        };

        $this->expectClosureOccasionallyReturnsNull($test);
    }
}
