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

use App\Mock\Generation\Value\Composite\ArrayGenerator\ArrayRegularValueGenerator;
use App\Mock\Generation\Value\Composite\ArrayGenerator\ArrayUniqueValueGenerator;
use App\Mock\Generation\Value\Composite\ArrayGenerator\DelegatingArrayValueGenerator;
use App\Mock\Parameters\Schema\Type\Composite\ArrayType;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class DelegatingArrayValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;
    use ProbabilityTestCaseTrait;

    /** @var ArrayRegularValueGenerator */
    private $regularValueGenerator;

    /** @var ArrayUniqueValueGenerator */
    private $uniqueValueGenerator;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
        $this->regularValueGenerator = \Phake::mock(ArrayRegularValueGenerator::class);
        $this->uniqueValueGenerator = \Phake::mock(ArrayUniqueValueGenerator::class);
    }

    /** @test */
    public function generateValue_arrayTypeIsNullable_nullReturned(): void
    {
        $generator = $this->createDelegatingArrayValueGenerator();
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
    public function generateValue_arrayItemsIsNotUnique_arrayGeneratedByRegularGenerator(): void
    {
        $generator = $this->createDelegatingArrayValueGenerator();
        $type = new ArrayType();
        $type->items = new DummyType();
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $generatedArray = $this->givenRegularValueGenerator_generateArray_returnsGeneratedArray();

        $value = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type->items);
        $this->assertRegularValueArrayWasGenerated($type);
        $this->assertSame($generatedArray, $value);
    }

    /** @test */
    public function generateValue_arrayItemsIsUnique_arrayGeneratedByUniqueGenerator(): void
    {
        $generator = $this->createDelegatingArrayValueGenerator();
        $type = new ArrayType();
        $type->uniqueItems = true;
        $type->items = new DummyType();
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $generatedArray = $this->givenUniqueValueGenerator_generateArray_returnsGeneratedArray();

        $value = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type->items);
        $this->assertUniqueValueArrayWasGenerated($type);
        $this->assertSame($generatedArray, $value);
    }

    private function createDelegatingArrayValueGenerator(): DelegatingArrayValueGenerator
    {
        return new DelegatingArrayValueGenerator($this->valueGeneratorLocator, $this->regularValueGenerator, $this->uniqueValueGenerator);
    }

    private function assertRegularValueArrayWasGenerated(ArrayType $type): void
    {
        \Phake::verify($this->regularValueGenerator)
            ->generateArray($this->valueGenerator, $type);

        \Phake::verify($this->uniqueValueGenerator, \Phake::never())
            ->generateArray(\Phake::anyParameters());
    }

    private function assertUniqueValueArrayWasGenerated(ArrayType $type): void
    {
        \Phake::verify($this->uniqueValueGenerator)
            ->generateArray($this->valueGenerator, $type);

        \Phake::verify($this->regularValueGenerator, \Phake::never())
            ->generateArray(\Phake::anyParameters());
    }

    private function givenRegularValueGenerator_generateArray_returnsGeneratedArray(): array
    {
        $generatedArray = ['generatedArray'];

        \Phake::when($this->regularValueGenerator)
            ->generateArray(\Phake::anyParameters())
            ->thenReturn($generatedArray);

        return $generatedArray;
    }

    private function givenUniqueValueGenerator_generateArray_returnsGeneratedArray(): array
    {
        $generatedArray = ['generatedUniqueArray'];

        \Phake::when($this->uniqueValueGenerator)
            ->generateArray(\Phake::anyParameters())
            ->thenReturn($generatedArray);

        return $generatedArray;
    }
}
