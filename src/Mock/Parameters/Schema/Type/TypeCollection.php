<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters\Schema\Type;

use Ramsey\Collection\AbstractCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class TypeCollection extends AbstractCollection
{
    public function getType(): string
    {
        return TypeInterface::class;
    }

    public function unserialize($serialized): void
    {
        $this->data = unserialize($serialized);
    }
}
