<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Faker\Provider;

use Faker\Provider\Base;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Base64Provider extends Base
{
    private const MINIMAL_LENGTH = 20;
    private const DEFAULT_LENGTH = 2000;

    public function base64(int $length = 0): string
    {
        if ($length <= self::MINIMAL_LENGTH) {
            $textLength = self::DEFAULT_LENGTH;
        } else {
            $textLength = (int) ($length * 2 / 3);
        }

        $text = $this->generator->text($textLength);

        return base64_encode($text);
    }
}
