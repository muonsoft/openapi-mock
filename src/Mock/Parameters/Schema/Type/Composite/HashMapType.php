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

use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class HashMapType extends ObjectType
{
    /** @var TypeMarkerInterface */
    public $value;

    /** @var int */
    public $minProperties = 0;

    /** @var int */
    public $maxProperties = 0;
}
