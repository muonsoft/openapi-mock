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

use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\Utility\StringList;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class StringType implements TypeMarkerInterface
{
    /** @var bool */
    public $nullable = false;

    /** @var int */
    public $minLength = 0;

    /** @var int */
    public $maxLength = 0;

    /** @var string */
    public $format = '';

    /** @var string */
    public $pattern = '';

    /** @var StringList */
    public $enum;

    public function __construct()
    {
        $this->enum = new StringList();
    }
}
