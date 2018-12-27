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
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\FieldParserTrait;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class IntegerTypeParser implements TypeParserInterface
{
    use FieldParserTrait;

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $type = new IntegerType();
        $schema = $specification->getSchema($pointer);

        $this->readFixedFieldsValues($type, $schema);
        $type->exclusiveMinimum = $this->readBoolValue($schema, 'exclusiveMinimum');
        $type->exclusiveMaximum = $this->readBoolValue($schema, 'exclusiveMaximum');
        $type->minimum = $this->readIntegerOrNullValue($schema, 'minimum');
        $type->maximum = $this->readIntegerOrNullValue($schema, 'maximum');
        $type->multipleOf = $this->readIntegerOrNullValue($schema, 'multipleOf');

        return $type;
    }
}
