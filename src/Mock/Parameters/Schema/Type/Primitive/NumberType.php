<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters\Schema\Type\Primitive;

use App\Mock\Parameters\Schema\Type\FixedFieldsTrait;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class NumberType implements TypeInterface
{
    use FixedFieldsTrait;

    /** @var float|null */
    public $minimum;

    /** @var float|null */
    public $maximum;

    /** @var bool */
    public $exclusiveMinimum = false;

    /** @var bool */
    public $exclusiveMaximum = false;

    /** @var float|null */
    public $multipleOf;
}
