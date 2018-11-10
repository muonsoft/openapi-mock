<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Faker;

use App\Faker\Provider\Base64Provider;
use App\Faker\Provider\TextProvider;
use Faker\Factory;
use Faker\Generator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class FakerGeneratorFactory
{
    public function createGenerator(): Generator
    {
        $generator = Factory::create();
        $generator->addProvider(new Base64Provider($generator));
        $generator->addProvider(new TextProvider($generator));

        return $generator;
    }
}
