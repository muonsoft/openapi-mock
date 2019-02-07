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
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\FieldParserTrait;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class StringTypeParser implements TypeParserInterface
{
    use FieldParserTrait;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

            $this->logger->warning(
                sprintf(
                    'Property "minLength" cannot be less than 0 at path "%s". Value is ignored.',
                    $pointer->getPath()
                )
            );
        }

        if ($type->maxLength < 0) {
            $type->maxLength = 0;

            $this->logger->warning(
                sprintf(
                    'Property "maxLength" cannot be less than 0 at path "%s". Value is ignored.',
                    $pointer->getPath()
                )
            );
        }

        if ($type->maxLength < $type->minLength && 0 !== $type->maxLength) {
            $type->maxLength = $type->minLength;

            $this->logger->warning(
                sprintf(
                    'Property "maxLength" cannot be greater than "minLength" as path "%s". Value is set to "minLength".',
                    $pointer->getPath()
                )
            );
        }
    }

    private function processEnumProperty(StringType $type, array $schema, SpecificationPointer $pointer): void
    {
        $enumValues = $schema['enum'] ?? [];

        if (!\is_array($enumValues)) {
            $this->logger->warning(
                sprintf(
                    'Invalid enum value "%s" detected in path "%s". Expected to be array of strings.',
                    json_encode($enumValues),
                    $pointer->getPath()
                )
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
                $this->logger->warning(
                    sprintf(
                        'Invalid enum value "%s" ignored in path "%s". Value must be valid string.',
                        json_encode($enumValue),
                        $pointer->getPath()
                    )
                );
            }
        }
    }
}
