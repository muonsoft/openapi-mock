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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait FixedFieldsTrait
{
    /** @var bool */
    public $nullable = false;

    /** @var bool */
    public $readOnly = false;

    /** @var bool */
    public $writeOnly = false;

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    public function isWriteOnly(): bool
    {
        return $this->writeOnly;
    }

    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    public function setReadOnly(bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    public function setWriteOnly(bool $writeOnly): void
    {
        $this->writeOnly = $writeOnly;
    }
}
