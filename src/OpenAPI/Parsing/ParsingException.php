<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing;

use Throwable;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ParsingException extends \DomainException
{
    public function __construct(string $message, SpecificationPointer $context, int $code = 0, Throwable $previous = null)
    {
        $messageWithPath = sprintf('Parsing error "%s" at path "%s".', $message, $context->getPath());

        parent::__construct($messageWithPath, $code, $previous);
    }
}
