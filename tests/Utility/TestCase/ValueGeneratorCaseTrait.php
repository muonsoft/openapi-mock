<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Utility\TestCase;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Generation\ValueGeneratorLocator;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait ValueGeneratorCaseTrait
{
    /** @var ValueGeneratorLocator */
    protected $valueGeneratorLocator;

    /** @var ValueGeneratorInterface */
    protected $valueGenerator;

    protected function setUpValueGenerator(): void
    {
        $this->valueGeneratorLocator = \Phake::mock(ValueGeneratorLocator::class);
        $this->valueGenerator = \Phake::mock(ValueGeneratorInterface::class);
    }

    protected function assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType(TypeMarkerInterface $type): void
    {
        \Phake::verify($this->valueGeneratorLocator)
            ->getValueGenerator($type);
    }

    protected function givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator(ValueGeneratorInterface $generator = null): void
    {
        if ($generator === null) {
            $generator = $this->valueGenerator;
        }

        \Phake::when($this->valueGeneratorLocator)
            ->getValueGenerator(\Phake::anyParameters())
            ->thenReturn($generator);
    }

    protected function assertValueGenerator_generateValue_wasCalledOnceWithType(TypeMarkerInterface $type): void
    {
        \Phake::verify($this->valueGenerator)
            ->generateValue($type);
    }

    protected function assertExpectedValueGenerator_generateValue_wasCalledOnceWithType(
        ValueGeneratorInterface $generator,
        TypeMarkerInterface $type
    ): void {
        \Phake::verify($generator)
            ->generateValue($type);
    }

    protected function assertValueGenerator_generateValue_wasCalledAtLeastOnceWithType(TypeMarkerInterface $type): void
    {
        \Phake::verify($this->valueGenerator, \Phake::atLeast(1))
            ->generateValue($type);
    }

    protected function givenValueGenerator_generateValue_returnsGeneratedValue(): string
    {
        $generatedValue = 'generated_value';

        \Phake::when($this->valueGenerator)
            ->generateValue(\Phake::anyParameters())
            ->thenReturn($generatedValue);

        return $generatedValue;
    }

    protected function givenValueGenerator_generateValue_returnsValue(ValueGeneratorInterface $generator, $value): void
    {
        \Phake::when($generator)
            ->generateValue(\Phake::anyParameters())
            ->thenReturn($value);
    }
}
