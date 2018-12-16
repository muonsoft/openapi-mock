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

    protected function assertFaker_method_wasCalledAtLeastOnce(string $method): void
    {
        \Phake::verify($this->faker, \Phake::atLeast(1))
            ->{$method}();
    }

    protected function assertFakerMock_method_wasCalledAtLeastOnce(Generator $faker, string $method): void
    {
        \Phake::verify($faker, \Phake::atLeast(1))
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

    protected function givenFakerMock_method_returnsValue(Generator $faker, string $method, $value): void
    {
        \Phake::when($faker)
            ->{$method}(\Phake::anyParameters())
            ->thenReturn($value);
    }

    protected function givenFaker_method_returnsNewFaker(string $method): Generator
    {
        $faker = \Phake::mock(Generator::class);

        \Phake::when($this->faker)
            ->{$method}(\Phake::anyParameters())
            ->thenReturn($faker);

        return $faker;
    }
}
