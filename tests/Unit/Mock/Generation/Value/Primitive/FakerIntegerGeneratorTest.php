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

use App\Mock\Generation\Value\Primitive\FakerIntegerGenerator;
use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Tests\Utility\TestCase\FakerCaseTrait;
use PHPUnit\Framework\TestCase;

class FakerIntegerGeneratorTest extends TestCase
{
    use FakerCaseTrait;

    protected function setUp(): void
    {
        $this->setUpFaker();
    }

    /** @test */
    public function generateValue_integerType_integerValueReturned(): void
    {
        $generator = new FakerIntegerGenerator($this->faker);
        $type = new IntegerType();

        $value = $generator->generateValue($type);

        $this->assertNotNull($value);
    }
}
