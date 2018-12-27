<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters\Schema\Type\Composite;

use App\Mock\Parameters\Schema\Type\FixedFieldsTrait;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class FreeFormObjectType implements TypeInterface
{
    use FixedFieldsTrait;

    /** @var int */
    public $minProperties = 0;

    /** @var int */
    public $maxProperties = 0;
}
