<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type\Primitive;

use App\Mock\Parameters\Schema\Type\Primitive\IntegerType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\Type\TypeParserInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class IntegerTypeParser implements TypeParserInterface
{
    public function parse(array $schema, ParsingContext $context): TypeMarkerInterface
    {
        $type = new IntegerType();

        $type->nullable = $this->readBoolValue($schema, 'nullable');
        $type->exclusiveMinimum = $this->readBoolValue($schema, 'exclusiveMinimum');
        $type->exclusiveMaximum = $this->readBoolValue($schema, 'exclusiveMaximum');
        $type->minimum = $this->readIntegerOrNullValue($schema, 'minimum');
        $type->maximum = $this->readIntegerOrNullValue($schema, 'maximum');
        $type->multipleOf = $this->readIntegerOrNullValue($schema, 'multipleOf');

        return $type;
    }

    private function readBoolValue(array $schema, string $key): bool
    {
        return (bool) ($schema[$key] ?? false);
    }

    private function readIntegerOrNullValue(array $schema, string $key): ?int
    {
        $value = null;

        if (array_key_exists($key, $schema)) {
            $value = (int) $schema[$key];
        }

        return $value;
    }
}
