<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Exception;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidTypeException extends MockGenerationException
{
    public function __construct()
    {
        parent::__construct('Trying to use invalid type for generation');
    }
}
