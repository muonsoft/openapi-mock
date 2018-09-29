<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation;

use App\Mock\Generation\DataGenerator;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Generation\ValueGeneratorLocator;
use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\Tests\Utility\DummyType;
use PHPUnit\Framework\TestCase;

class DataGeneratorTest extends TestCase
{
    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    /** @var ValueGeneratorInterface */
    private $valueGenerator;

    protected function setUp(): void
    {
        $this->generatorLocator = \Phake::mock(ValueGeneratorLocator::class);
        $this->valueGenerator = \Phake::mock(ValueGeneratorInterface::class);
    }

    /** @test */
    public function generateData_given_expected(): void
    {
        $generator = new DataGenerator($this->generatorLocator);
        $type = new DummyType();
        $schema = $this->givenSchemaWithValueType($type);
        $generatedValue = $this->givenValueGenerator_generateValue_returnsValue();
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();

        $value = $generator->generateData($schema);

        $this->assertValueGeneratorLocator_getValueGenerator_isCalledOnceWithType($type);
        $this->assertValueGenerator_generateValue_isCalledOnceWithType($type);
        $this->assertSame($generatedValue, $value);
    }

    private function assertValueGenerator_generateValue_isCalledOnceWithType(TypeMarkerInterface $type): void
    {
        \Phake::verify($this->valueGenerator)
            ->generateValue($type);
    }

    private function givenValueGenerator_generateValue_returnsValue(): string
    {
        $generatedValue = 'generated_value';
        \Phake::when($this->valueGenerator)
            ->generateValue(\Phake::anyParameters())
            ->thenReturn($generatedValue);

        return $generatedValue;
    }

    private function givenSchemaWithValueType(TypeMarkerInterface $type): Schema
    {
        $schema = new Schema();
        $schema->value = $type;

        return $schema;
    }

    private function assertValueGeneratorLocator_getValueGenerator_isCalledOnceWithType(TypeMarkerInterface $type): void
    {
        \Phake::verify($this->generatorLocator)
            ->getValueGenerator($type);
    }

    private function givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator(): void
    {
        \Phake::when($this->generatorLocator)
            ->getValueGenerator(\Phake::anyParameters())
            ->thenReturn($this->valueGenerator);
    }
}
