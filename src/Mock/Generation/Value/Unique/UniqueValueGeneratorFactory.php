<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Unique;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UniqueValueGeneratorFactory
{
    public function createGenerator(ValueGeneratorInterface $generator, TypeInterface $type): UniqueValueGenerator
    {
        return new UniqueValueGenerator($generator, $type);
    }
}
