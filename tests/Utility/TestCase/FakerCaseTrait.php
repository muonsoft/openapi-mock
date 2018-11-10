<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Utility\TestCase;

use Faker\Generator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait FakerCaseTrait
{
    /** @var Generator */
    protected $faker;

    protected function setUpFaker(): void
    {
        $this->faker = \Phake::mock(Generator::class);
    }

    protected function assertFaker_method_wasCalledOnce(string $method): void
    {
        \Phake::verify($this->faker)
            ->{$method}();
    }

    protected function assertFaker_method_wasCalledOnceWithParameter(string $method, $parameter): void
    {
        \Phake::verify($this->faker)
            ->{$method}($parameter);
    }

    protected function assertFaker_method_wasCalledOnceWithTwoParameters(string $method, $parameter1, $parameter2): void
    {
        \Phake::verify($this->faker)
            ->{$method}($parameter1, $parameter2);
    }

    protected function givenFaker_method_returnsValue(string $method, $value): void
    {
        \Phake::when($this->faker)
            ->{$method}(\Phake::anyParameters())
            ->thenReturn($value);
    }
}
