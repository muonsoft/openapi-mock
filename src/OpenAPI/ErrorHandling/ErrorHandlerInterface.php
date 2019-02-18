<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\ErrorHandling;

use App\OpenAPI\Parsing\SpecificationPointer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface ErrorHandlerInterface
{
    public function reportError(string $message, SpecificationPointer $pointer): string;

    public function reportWarning(string $message, SpecificationPointer $pointer): string;
}
