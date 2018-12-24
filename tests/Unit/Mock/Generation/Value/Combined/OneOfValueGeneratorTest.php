<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Combined;

use App\Mock\Generation\Value\Combined\OneOfValueGenerator;
use App\Mock\Parameters\Schema\Type\Combined\OneOfType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

class OneOfValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateValue_oneOfWithTwoTypes_oneOfValuesGeneratedAndReturned(): void
    {
        $generator = new OneOfValueGenerator($this->valueGeneratorLocator);
        $oneOf = new OneOfType();
        $type1 = \Phake::mock(TypeMarkerInterface::class);
        $type2 = \Phake::mock(TypeMarkerInterface::class);
        $oneOf->types->add($type1);
        $oneOf->types->add($type2);
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $expectedValue = $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $value = $generator->generateValue($oneOf);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithOneOfTypes($type1, $type2);
        $this->assertValueGenerator_generateValue_wasCalledOnceWithOneOfTypes($type1, $type2);
        $this->assertSame($expectedValue, $value);
    }

    private function assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithOneOfTypes(
        TypeMarkerInterface $type1,
        TypeMarkerInterface $type2
    ): void {
        \Phake::verify($this->valueGeneratorLocator)
            ->getValueGenerator(\Phake::capture($type));
        $this->assertTrue($type === $type1 || $type === $type2);
    }

    private function assertValueGenerator_generateValue_wasCalledOnceWithOneOfTypes(
        TypeMarkerInterface $type1,
        TypeMarkerInterface $type2
    ): void {
        \Phake::verify($this->valueGenerator)
            ->generateValue(\Phake::capture($type));
        $this->assertTrue($type === $type1 || $type === $type2);
    }
}
