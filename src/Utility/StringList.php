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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class StringList extends ArrayCollection
{
    public function __construct(array $elements = [])
    {
        foreach ($elements as $key => $value) {
            $elements[$key] = (string) $value;
        }
        parent::__construct($elements);
    }

    /**
     * @param string $element
     * @return bool
     */
    public function add($element): bool
    {
        return parent::add((string) $element);
    }

    public function set($key, $value): void
    {
        parent::set($key, (string) $value);
    }
}
