<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters\Schema\Type;

use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface TypeInterface extends SpecificationObjectMarkerInterface
{
    public function isNullable(): bool;

    public function isReadOnly(): bool;

    public function isWriteOnly(): bool;

    public function setNullable(bool $nullable): void;

    public function setReadOnly(bool $readOnly): void;

    public function setWriteOnly(bool $writeOnly): void;
}
