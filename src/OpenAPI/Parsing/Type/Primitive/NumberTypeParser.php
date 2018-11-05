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

use App\Mock\Parameters\Schema\Type\Primitive\NumberType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\ParsingContext;
use App\OpenAPI\Parsing\Type\TypeParserInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class NumberTypeParser implements TypeParserInterface
{
    public function parse(array $schema, ParsingContext $context): TypeMarkerInterface
    {
        $type = new NumberType();

        $type->nullable = $this->readBoolValue($schema, 'nullable');
        $type->exclusiveMinimum = $this->readBoolValue($schema, 'exclusiveMinimum');
        $type->exclusiveMaximum = $this->readBoolValue($schema, 'exclusiveMaximum');
        $type->minimum = $this->readFloatOrNullValue($schema, 'minimum');
        $type->maximum = $this->readFloatOrNullValue($schema, 'maximum');
        $type->multipleOf = $this->readFloatOrNullValue($schema, 'multipleOf');

        return $type;
    }

    private function readBoolValue(array $schema, string $key): bool
    {
        return (bool) ($schema[$key] ?? false);
    }

    private function readFloatOrNullValue(array $schema, string $key): ?float
    {
        $value = null;

        if (array_key_exists($key, $schema)) {
            $value = (float) $schema[$key];
        }

        return $value;
    }
}
