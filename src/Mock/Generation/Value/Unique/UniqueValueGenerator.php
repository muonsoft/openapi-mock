<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Unique;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\TypeInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UniqueValueGenerator
{
    private const MAX_ATTEMPTS = 100;

    /** @var ValueGeneratorInterface */
    private $generator;

    /** @var TypeInterface */
    private $type;

    /** @var string[] */
    private $uniques = [];

    /** @var int */
    private $attempts = 0;

    public function __construct(ValueGeneratorInterface $generator, TypeInterface $type)
    {
        $this->generator = $generator;
        $this->type = $type;
    }

    public function nextValue()
    {
        $this->attempts = 0;

        do {
            $value = $this->generator->generateValue($this->type);
            $this->attempts++;

            if ($this->isAttemptsExceedingLimit()) {
                break;
            }
        } while ($this->isNotUnique($value));

        $this->appendToUniqueValues($value);

        return $value;
    }

    public function isAttemptsExceedingLimit(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    private function isNotUnique($value): bool
    {
        $hash = md5(json_encode($value));

        return \in_array($hash, $this->uniques, true);
    }

    private function appendToUniqueValues($value): void
    {
        $this->uniques[] = md5(json_encode($value));
    }
}
