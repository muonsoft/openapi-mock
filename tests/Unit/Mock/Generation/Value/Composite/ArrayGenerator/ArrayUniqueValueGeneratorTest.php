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
use App\Mock\Generation\Value\Unique\UniqueValueGenerator;
use App\Mock\Generation\Value\Unique\UniqueValueGeneratorFactory;
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

    private const VALUE = 'value';

    /** @var UniqueValueGeneratorFactory */
    private $uniqueValueGeneratorFactory;

    /** @var UniqueValueGenerator */
    private $uniqueValueGenerator;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
        $this->uniqueValueGeneratorFactory = \Phake::mock(UniqueValueGeneratorFactory::class);
        $this->uniqueValueGenerator = \Phake::mock(UniqueValueGenerator::class);

        \Phake::when($this->uniqueValueGeneratorFactory)
            ->createGenerator(\Phake::anyParameters())
            ->thenReturn($this->uniqueValueGenerator);
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
        $this->givenUniqueValueGenerator_nextValues_returnsValue(self::VALUE);
        $this->givenUniqueValueGenerator_isAttemptsExceedingLimit_returnsFalse();

        $array = $generator->generateArray($this->valueGenerator, $type);

        $this->assertUniqueValueGeneratorCreated($this->valueGenerator, $type->items);
        $this->assertUniqueValueGenerator_nextValue_wasCalledTimes(3);
        $this->assertUniqueValueGenerator_isAttemptsExceedingLimit_wasCalledTimes(3);
        $this->assertLengthGeneratedInRange(1, 3);
        $this->assertCount(3, $array);
        $this->assertSame(self::VALUE, $array[0]);
        $this->assertSame(self::VALUE, $array[1]);
        $this->assertSame(self::VALUE, $array[2]);
    }

    /** @test */
    public function generateArray_valueGeneratorExceedsUniqueAttemptsAndMinLengthNotReached_exceptionThrown(): void
    {
        $generator = $this->createArrayUniqueValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $this->givenLengthGeneratorGeneratesLength(3, 3);
        $this->givenUniqueValueGenerator_isAttemptsExceedingLimit_returnsTrue();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot generate array with unique values, attempts limit exceeded');

        $generator->generateArray($this->valueGenerator, $type);
    }

    /** @test */
    public function generateArray_valueGeneratorExceedsUniqueAttemptsAndMinLengthReached_arrayReducedToMinLength(): void
    {
        $generator = $this->createArrayUniqueValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $this->givenLengthGeneratorGeneratesLength(3, 2);
        $this->givenUniqueValueGenerator_isAttemptsExceedingLimit_returnsSequence(false, false, true);

        $array = $generator->generateArray($this->valueGenerator, $type);

        $this->assertCount(2, $array);
        $this->assertUniqueValueGenerator_isAttemptsExceedingLimit_wasCalledTimes(3);
    }

    private function createArrayUniqueValueGenerator(): ArrayUniqueValueGenerator
    {
        return new ArrayUniqueValueGenerator($this->lengthGenerator, $this->uniqueValueGeneratorFactory);
    }

    private function assertUniqueValueGeneratorCreated(ValueGeneratorInterface $generator, TypeInterface $type): void
    {
        \Phake::verify($this->uniqueValueGeneratorFactory)
            ->createGenerator($generator, $type);
    }

    private function assertUniqueValueGenerator_nextValue_wasCalledTimes(int $times): void
    {
        \Phake::verify($this->uniqueValueGenerator, \Phake::times($times))
            ->nextValue();
    }

    private function assertUniqueValueGenerator_isAttemptsExceedingLimit_wasCalledTimes(int $times): void
    {
        \Phake::verify($this->uniqueValueGenerator, \Phake::times($times))
            ->isAttemptsExceedingLimit();
    }

    private function givenUniqueValueGenerator_nextValues_returnsValue(string $value): void
    {
        \Phake::when($this->uniqueValueGenerator)
            ->nextValue(\Phake::anyParameters())
            ->thenReturn($value);
    }

    private function givenUniqueValueGenerator_isAttemptsExceedingLimit_returnsFalse(): void
    {
        \Phake::when($this->uniqueValueGenerator)
            ->isAttemptsExceedingLimit(\Phake::anyParameters())
            ->thenReturn(false);
    }

    private function givenUniqueValueGenerator_isAttemptsExceedingLimit_returnsTrue(): void
    {
        \Phake::when($this->uniqueValueGenerator)
            ->isAttemptsExceedingLimit(\Phake::anyParameters())
            ->thenReturn(true);
    }

    private function givenUniqueValueGenerator_isAttemptsExceedingLimit_returnsSequence(bool ...$values): void
    {
        $mock = \Phake::when($this->uniqueValueGenerator)->isAttemptsExceedingLimit(\Phake::anyParameters());

        foreach ($values as $value) {
            $mock = $mock->thenReturn($value);
        }
    }
}
