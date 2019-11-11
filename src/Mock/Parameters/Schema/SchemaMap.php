<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters\Schema;

use Ramsey\Collection\Map\AbstractTypedMap;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SchemaMap extends AbstractTypedMap
{
    public function getKeyType(): string
    {
        return 'string';
    }

    public function getValueType(): string
    {
        return Schema::class;
    }

    public function unserialize($serialized): void
    {
        $this->data = unserialize($serialized);
    }
}
