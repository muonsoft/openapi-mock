<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters\Schema\Type\Combined;

use App\Mock\Parameters\Schema\Type\FixedFieldsTrait;
use App\Mock\Parameters\Schema\Type\TypeCollection;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
abstract class AbstractCombinedType implements TypeInterface
{
    use FixedFieldsTrait;

    /** @var TypeCollection */
    public $types;

    public function __construct()
    {
        $this->types = new TypeCollection();
    }
}
