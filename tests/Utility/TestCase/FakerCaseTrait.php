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

use Faker\Factory;
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
        $this->faker = Factory::create();
    }
}
