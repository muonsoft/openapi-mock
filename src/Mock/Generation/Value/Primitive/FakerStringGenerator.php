<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Primitive;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use Faker\Generator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class FakerStringGenerator implements ValueGeneratorInterface
{
    private const DEFAULT_MAX_LENGTH = 200;
    private const FAKER_METHOD_MAP = [
        'date' => 'date',
        'date-time' => 'dateTime',
        'uuid' => 'uuid',
        'email' => 'email',
        'uri' => 'url',
        'hostname' => 'domainName',
        'ipv4' => 'ipv4',
        'ipv6' => 'ipv6',
        'byte' => 'base64',
    ];

    /** @var Generator */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    /**
     * @param StringType $type
     * @return null|string
     * @throws \Exception
     */
    public function generateValue(TypeMarkerInterface $type): ?string
    {
        if ($type->nullable && random_int(0, 1) === 0) {
            $value = null;
        } else {
            $value = $this->generateStringValue($type);
        }

        return $value;
    }

    private function generateStringValue(StringType $type)
    {
        if ($type->enum->count() > 0) {
            $value = $this->generateRandomEnumValue($type);
        } elseif ($type->pattern !== '') {
            $value = $this->generateValueByPattern($type);
        } elseif ($this->typeHasSupportedFormat($type)) {
            $value = $this->generateValueByFormat($type);
        } else {
            $value = $this->generateText($type);
        }

        return $value;
    }

    private function generateRandomEnumValue(StringType $type)
    {
        $randomArrayKey = array_rand($type->enum->toArray());

        return $type->enum->get($randomArrayKey);
    }

    private function generateValueByPattern(StringType $type): string
    {
        return $this->faker->regexify($type->pattern);
    }

    private function typeHasSupportedFormat(StringType $type): bool
    {
        return $type->format !== '' && array_key_exists($type->format, self::FAKER_METHOD_MAP);
    }

    private function generateValueByFormat(StringType $type): string
    {
        $fakerMethod = self::FAKER_METHOD_MAP[$type->format];
        $fakerMethodParameters = [];

        if ($type->format === 'byte') {
            $fakerMethodParameters[] = $type->maxLength;
        }

        $value = \call_user_func_array([$this->faker, $fakerMethod], $fakerMethodParameters);

        if ($value instanceof \DateTime) {
            $dateFormat = $type->format === 'date' ? 'Y-m-d' : \DateTime::ATOM;
            $value = $value->format($dateFormat);
        }

        return $value;
    }

    private function generateText(StringType $type): string
    {
        $maxLength = $type->maxLength > 0 ? $type->maxLength : self::DEFAULT_MAX_LENGTH;

        return $this->faker->rangedText($type->minLength, $maxLength);
    }
}
