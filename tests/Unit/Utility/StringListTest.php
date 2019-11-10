<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Unit\Utility;

use App\Utility\StringList;
use PHPUnit\Framework\TestCase;

class StringListTest extends TestCase
{
    private const STRING_VALUE = 'a';

    /** @test */
    public function construct_givenString_stringInCollection(): void
    {
        $list = new StringList([self::STRING_VALUE]);

        $this->assertContains(self::STRING_VALUE, $list->toArray());
    }

    /** @test */
    public function add_givenString_stringValueIsInCollection(): void
    {
        $list = new StringList();

        $list->add(self::STRING_VALUE);

        $this->assertContains(self::STRING_VALUE, $list);
    }
}
