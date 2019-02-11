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

use App\Mock\Exception\InvalidTypeException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidType implements TypeInterface
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

    public function isNullable(): bool
    {
        throw new InvalidTypeException();
    }

    public function isReadOnly(): bool
    {
        throw new InvalidTypeException();
    }

    public function isWriteOnly(): bool
    {
        throw new InvalidTypeException();
    }

    public function setNullable(bool $nullable): void
    {
        throw new InvalidTypeException();
    }

    public function setReadOnly(bool $readOnly): void
    {
        throw new InvalidTypeException();
    }

    public function setWriteOnly(bool $writeOnly): void
    {
        throw new InvalidTypeException();
    }
}
