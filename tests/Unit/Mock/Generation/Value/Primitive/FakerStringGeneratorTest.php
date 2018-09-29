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

use App\Mock\Generation\Value\Primitive\FakerStringGenerator;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\Tests\Utility\TestCase\FakerCaseTrait;
use PHPUnit\Framework\TestCase;

class FakerStringGeneratorTest extends TestCase
{
    use FakerCaseTrait;

    protected function setUp(): void
    {
        $this->setUpFaker();
    }

    /** @test */
    public function generateValue_stringType_stringValueReturn(): void
    {
        $generator = new FakerStringGenerator($this->faker);
        $type = new StringType();

        $value = $generator->generateValue($type);

        $this->assertNotNull($value);
    }
}
