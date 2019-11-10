<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utility;

use Ramsey\Collection\AbstractCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class StringList extends AbstractCollection
{
    public function getType(): string
    {
        return 'string';
    }
}
