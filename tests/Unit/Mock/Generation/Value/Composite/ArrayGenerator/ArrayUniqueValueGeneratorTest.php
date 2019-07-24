<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Composite\ArrayGenerator;

use App\Mock\Generation\Value\Composite\ArrayGenerator\ArrayUniqueValueGenerator;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ArrayUniqueValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateArray_valueGeneratorCanGenerateUniqueValues_uniqueValuesGenerated(): void
    {
        $generator = $this->createArrayUniqueValueGenerator();
        $type = new ArrayType();
        $type->minItems = 1;
        $type->maxItems = 3;
        $type->items = new DummyType();
        $this->givenLengthGeneratorGeneratesLength(3, 3);
        $randomRangeValueGenerator = $this->givenRandomRangeValueGenerator(0, 2);

        $array = $generator->generateArray($randomRangeValueGenerator, $type);

        $this->assertLengthGeneratedInRange(1, 3);
        $this->assertContains(0, $array);
        $this->assertContains(1, $array);
        $this->assertContains(2, $array);
    }

    /** @test */
    public function generateArray_valueGeneratorExceedsUniqueAttemptsAndMinLengthNotReached_exceptionThrown(): void
    {
        $generator = $this->createArrayUniqueValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $this->givenLengthGeneratorGeneratesLength(3, 3);
        $randomRangeValueGenerator = $this->givenRandomRangeValueGenerator(0, 1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot generate array with unique values, attempts limit exceeded');

        $generator->generateArray($randomRangeValueGenerator, $type);
    }

    /** @test */
    public function generateArray_valueGeneratorExceedsUniqueAttemptsAndMinLengthReached_arrayReducedToMinLength(): void
    {
        $generator = $this->createArrayUniqueValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $this->givenLengthGeneratorGeneratesLength(3, 2);
        $randomRangeValueGenerator = $this->givenRandomRangeValueGenerator(0, 1);

        $array = $generator->generateArray($randomRangeValueGenerator, $type);

        $this->assertCount(2, $array);
    }

    private function givenRandomRangeValueGenerator(int $min, int $max): ValueGeneratorInterface
    {
        return new class($min, $max) implements ValueGeneratorInterface {
            /** @var int */
            private $min;

            /** @var int */
            private $max;

            public function __construct(int $min, int $max)
            {
                $this->min = $min;
                $this->max = $max;
            }

            public function generateValue(TypeInterface $type): int
            {
                return random_int($this->min, $this->max);
            }
        };
    }

    private function createArrayUniqueValueGenerator(): ArrayUniqueValueGenerator
    {
        return new ArrayUniqueValueGenerator($this->lengthGenerator);
    }
}
