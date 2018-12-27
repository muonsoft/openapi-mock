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
use App\Mock\Parameters\Schema\Type\TypeCollection;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\Utility\StringList;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ObjectType implements TypeInterface
{
    use FixedFieldsTrait;

    /** @var StringList */
    public $required;

    /** @var TypeCollection */
    public $properties;

    public function __construct()
    {
        $this->required = new StringList();
        $this->properties = new TypeCollection();
    }
}
