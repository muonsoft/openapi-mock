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

use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\FieldParserTrait;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class StringTypeParser implements TypeParserInterface
{
    use FieldParserTrait;

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    public function __construct(ParsingErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $type = new StringType();
        $schema = $specification->getSchema($pointer);

        $this->readFixedFieldsValues($type, $schema);
        $type->minLength = $this->readIntegerValue($schema, 'minLength');
        $type->maxLength = $this->readIntegerValue($schema, 'maxLength');
        $type->format = $this->readStringValue($schema, 'format');
        $type->pattern = $this->readStringValue($schema, 'pattern');

        $this->lengthsAutoCorrection($type, $pointer);
        $this->processEnumProperty($type, $schema, $pointer);

        return $type;
    }

    private function lengthsAutoCorrection(StringType $type, SpecificationPointer $pointer): void
    {
        if ($type->minLength < 0) {
            $type->minLength = 0;

            $this->errorHandler->reportWarning('Property "minLength" cannot be less than 0. Value is ignored.', $pointer);
        }

        if ($type->maxLength < 0) {
            $type->maxLength = 0;

            $this->errorHandler->reportWarning('Property "maxLength" cannot be less than 0. Value is ignored.', $pointer);
        }

        if ($type->maxLength < $type->minLength && 0 !== $type->maxLength) {
            $type->maxLength = $type->minLength;

            $this->errorHandler->reportWarning('Property "maxLength" cannot be greater than "minLength". Value is set to "minLength".', $pointer);
        }
    }

    private function processEnumProperty(StringType $type, array $schema, SpecificationPointer $pointer): void
    {
        $enumValues = $schema['enum'] ?? [];

        if (!\is_array($enumValues)) {
            $this->errorHandler->reportWarning(
                sprintf('Invalid enum value "%s". Expected to be array of strings.', json_encode($enumValues)),
                $pointer
            );
        } else {
            $this->processEnumValues($type, $enumValues, $pointer);
        }
    }

    private function processEnumValues(StringType $type, array $enumValues, SpecificationPointer $pointer): void
    {
        foreach ($enumValues as $enumValue) {
            if (\is_string($enumValue)) {
                $type->enum->add($enumValue);
            } else {
                $this->errorHandler->reportWarning(
                    sprintf('Invalid enum value "%s" ignored. Value must be valid string.', json_encode($enumValue)),
                    $pointer
                );
            }
        }
    }
}
