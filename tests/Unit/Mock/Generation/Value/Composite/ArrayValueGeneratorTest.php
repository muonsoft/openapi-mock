<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Composite;

use App\Mock\Generation\Value\Composite\ArrayValueGenerator;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

class ArrayValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;
    use ProbabilityTestCaseTrait;

    private const DEFAULT_MIN_ITEMS = 1;
    private const DEFAULT_MAX_ITEMS = 20;
    private const ARRAY_SIZE = 25;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateValue_arrayTypeIsNullable_nullReturned(): void
    {
        $generator = $this->createArrayValueGenerator();
        $type = new ArrayType();
        $type->nullable = true;
        $type->items = new DummyType();
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $test = function () use ($generator, $type) {
            return $generator->generateValue($type);
        };

        $this->expectClosureOccasionallyReturnsNull($test);
    }

    /** @test */
    public function generateValue_arrayTypeWithItemsTypeAndDefaultSize_arrayOfTypedItemsGeneratedAndReturned(): void
    {
        $generator = $this->createArrayValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $value = $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $array = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type->items);
        $this->assertValueGenerator_generateValue_wasCalledAtLeastOnceWithType($type->items);
        $this->assertContains($value, $array);
        $this->assertGreaterThanOrEqual(self::DEFAULT_MIN_ITEMS, \count($array));
        $this->assertLessThanOrEqual(self::DEFAULT_MAX_ITEMS, \count($array));
    }

    /** @test */
    public function generateValue_arrayTypeWithItemsTypeAndGivenSize_arrayOfGivenSizeGeneratedAndReturned(): void
    {
        $generator = $this->createArrayValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $type->minItems = self::ARRAY_SIZE;
        $type->maxItems = self::ARRAY_SIZE;
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $array = $generator->generateValue($type);

        $this->assertCount(self::ARRAY_SIZE, $array);
    }

    /** @test */
    public function generateValue_arrayTypeWithUniqueItems_arrayWithUniqueItemsGeneratedAndReturned(): void
    {
        $generator = $this->createArrayValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $type->minItems = 3;
        $type->maxItems = 3;
        $type->uniqueItems = true;
        $randomRangeValueGenerator = $this->givenRandomRangeValueGenerator(0, 2);
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator($randomRangeValueGenerator);

        $array = $generator->generateValue($type);

        $this->assertContains(0, $array);
        $this->assertContains(1, $array);
        $this->assertContains(2, $array);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot generate array with unique values, attempts limit exceeded
     */
    public function generateValue_arrayTypeWithUniqueItems_exceptionThrownOnRetryLimit(): void
    {
        $generator = $this->createArrayValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $type->minItems = 3;
        $type->maxItems = 3;
        $type->uniqueItems = true;
        $randomRangeValueGenerator = $this->givenRandomRangeValueGenerator(0, 1);
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator($randomRangeValueGenerator);

        $generator->generateValue($type);
    }

    private function createArrayValueGenerator(): ArrayValueGenerator
    {
        return new ArrayValueGenerator($this->valueGeneratorLocator);
    }

    private function givenRandomRangeValueGenerator(int $min, int $max): ValueGeneratorInterface
    {
        return new class ($min, $max) implements ValueGeneratorInterface
        {
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
}
