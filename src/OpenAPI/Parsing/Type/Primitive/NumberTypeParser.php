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
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\FieldParserTrait;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class NumberTypeParser implements TypeParserInterface
{
    use FieldParserTrait;

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $type = new NumberType();
        $schema = $specification->getSchema($pointer);

        $this->readFixedFieldsValues($type, $schema);
        $type->nullable = $this->readBoolValue($schema, 'nullable');
        $type->exclusiveMinimum = $this->readBoolValue($schema, 'exclusiveMinimum');
        $type->exclusiveMaximum = $this->readBoolValue($schema, 'exclusiveMaximum');
        $type->minimum = $this->readFloatOrNullValue($schema, 'minimum');
        $type->maximum = $this->readFloatOrNullValue($schema, 'maximum');
        $type->multipleOf = $this->readFloatOrNullValue($schema, 'multipleOf');

        return $type;
    }
}
