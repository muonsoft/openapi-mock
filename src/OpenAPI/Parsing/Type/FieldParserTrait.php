<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type;

use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait FieldParserTrait
{
    protected function readFixedFieldsValues(TypeInterface $type, array $schema): void
    {
        $type->setNullable($this->readBoolValue($schema, 'nullable'));
        $type->setReadOnly($this->readBoolValue($schema, 'readOnly'));
        $type->setWriteOnly($this->readBoolValue($schema, 'writeOnly'));
    }

    protected function readBoolValue(array $schema, string $key): bool
    {
        return (bool)($schema[$key] ?? false);
    }

    protected function readIntegerOrNullValue(array $schema, string $key): ?int
    {
        $value = null;

        if (array_key_exists($key, $schema)) {
            $value = (int)$schema[$key];
        }

        return $value;
    }

    protected function readFloatOrNullValue(array $schema, string $key): ?float
    {
        $value = null;

        if (array_key_exists($key, $schema)) {
            $value = (float)$schema[$key];
        }

        return $value;
    }

    protected function readIntegerValue(array $schema, string $key): int
    {
        return (int)($schema[$key] ?? 0);
    }

    protected function readStringValue(array $schema, string $key): string
    {
        return (string)($schema[$key] ?? '');
    }
}
