<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Error;

use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationPointer;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ExceptionalErrorHandler implements ParsingErrorHandlerInterface
{
    public function reportError(string $message, SpecificationPointer $pointer): string
    {
        throw new ParsingException($message, $pointer);
    }

    public function reportWarning(string $message, SpecificationPointer $pointer): string
    {
        throw new ParsingException($message, $pointer);
    }
}
