<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters;

use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidObject implements SpecificationObjectMarkerInterface
{
    /** @var string */
    private $error;

    public function __construct(string $error)
    {
        $this->error = $error;
    }

    public function getError(): string
    {
        return $this->error;
    }
}
