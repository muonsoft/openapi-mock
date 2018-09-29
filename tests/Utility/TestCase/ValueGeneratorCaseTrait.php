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

    protected function assertValueGeneratorLocator_getValueGenerator_isCalledOnceWithType(TypeMarkerInterface $type): void
    {
        \Phake::verify($this->valueGeneratorLocator)
            ->getValueGenerator($type);
    }

    protected function givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator(): void
    {
        \Phake::when($this->valueGeneratorLocator)
            ->getValueGenerator(\Phake::anyParameters())
            ->thenReturn($this->valueGenerator);
    }

    protected function assertValueGenerator_generateValue_isCalledOnceWithType(TypeMarkerInterface $type): void
    {
        \Phake::verify($this->valueGenerator)
            ->generateValue($type);
    }

    protected function givenValueGenerator_generateValue_returnsValue(): string
    {
        $generatedValue = 'generated_value';

        \Phake::when($this->valueGenerator)
            ->generateValue(\Phake::anyParameters())
            ->thenReturn($generatedValue);

        return $generatedValue;
    }
}
