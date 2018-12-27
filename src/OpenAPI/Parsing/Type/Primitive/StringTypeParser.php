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

    /** @var StringType */
    private $type;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $this->type = new StringType();
        $schema = $specification->getSchema($pointer);

        $this->readFixedFieldsValues($this->type, $schema);
        $this->type->minLength = $this->readIntegerValue($schema, 'minLength');
        $this->type->maxLength = $this->readIntegerValue($schema, 'maxLength');
        $this->type->format = $this->readStringValue($schema, 'format');
        $this->type->pattern = $this->readStringValue($schema, 'pattern');

        $this->lengthsAutoCorrection($pointer);

        $this->processEnumProperty($schema, $pointer);

        return $this->type;
    }

    private function lengthsAutoCorrection(SpecificationPointer $pointer): void
    {
        if ($this->type->minLength < 0) {
            $this->type->minLength = 0;

            $this->logger->warning(
                sprintf(
                    'Property "minLength" cannot be less than 0 at path "%s". Value is ignored.',
                    $pointer->getPath()
                )
            );
        }

        if ($this->type->maxLength < 0) {
            $this->type->maxLength = 0;

            $this->logger->warning(
                sprintf(
                    'Property "maxLength" cannot be less than 0 at path "%s". Value is ignored.',
                    $pointer->getPath()
                )
            );
        }

        if ($this->type->maxLength < $this->type->minLength && $this->type->maxLength !== 0) {
            $this->type->maxLength = $this->type->minLength;

            $this->logger->warning(
                sprintf(
                    'Property "maxLength" cannot be greater than "minLength" as path "%s". Value is set to "minLength".',
                    $pointer->getPath()
                )
            );
        }
    }

    private function processEnumProperty(array $schema, SpecificationPointer $pointer): void
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
            $this->processEnumValues($enumValues, $pointer);
        }
    }

    private function processEnumValues(array $enumValues, SpecificationPointer $pointer): void
    {
        foreach ($enumValues as $enumValue) {
            if (\is_string($enumValue)) {
                $this->type->enum->add($enumValue);
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
