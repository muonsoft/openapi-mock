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
class TextProvider extends Base
{
    private const MAX_ATTEMPTS = 10;

    public function rangedText(int $minLength, int $maxLength): string
    {
        if ($maxLength < $minLength) {
            throw new \InvalidArgumentException('Max length cannot be less than min length');
        }

        if ($maxLength < 5) {
            $value = $this->generateTextOfStrictLength($maxLength);
        } else {
            $value = $this->tryToGenerateTextOfLength($minLength, $maxLength);

            if (\strlen($value) < $minLength) {
                $value = $this->generateTextOfStrictLength($minLength);
            }
        }

        return $value;
    }

    private function generateTextOfStrictLength(int $length): string
    {
        $lexifyString = $this->getLexifyStringOfLength($length);

        return $this->generator->lexify($lexifyString);
    }

    private function getLexifyStringOfLength(int $length): string
    {
        $s = '';

        for ($i = 0; $i < $length; ++$i) {
            $s .= '?';
        }

        return $s;
    }

    private function tryToGenerateTextOfLength(int $minLength, int $maxLength): string
    {
        $value = '';

        for ($attempts = 0; $attempts < self::MAX_ATTEMPTS; ++$attempts) {
            $value = $this->generator->text($maxLength);

            if (\strlen($value) > $minLength) {
                break;
            }
        }

        return $value;
    }
}
