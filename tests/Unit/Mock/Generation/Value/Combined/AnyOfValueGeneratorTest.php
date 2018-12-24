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

use App\Mock\Generation\Value\Combined\AnyOfValueGenerator;
use App\Mock\Parameters\Schema\Type\Combined\AnyOfType;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

class AnyOfValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    private const GENERATED_VALUE_1 = [
        'commonProperty' => 'commonPropertyValue',
        'property1' => 'value1',
    ];
    private const GENERATED_VALUE_2 = [
        'commonProperty' => 'commonPropertyValue',
        'property2' => 'value2',
    ];
    private const MERGED_GENERATED_VALUE = [
        'commonProperty' => 'commonPropertyValue',
        'property1' => 'value1',
        'property2' => 'value2',
    ];
    private const COMMON_PROPERTY_PAIR = [
        'commonProperty' => 'commonPropertyValue',
    ];

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateValue_anyOfWithTwoTypes_generatorsLocatedAndUsedAtMostOnceAndValueHasCommonProperty(): void
    {
        $generator = new AnyOfValueGenerator($this->valueGeneratorLocator);
        $anyOf = new AnyOfType();
        $type1 = \Phake::mock(TypeMarkerInterface::class);
        $type2 = \Phake::mock(TypeMarkerInterface::class);
        $anyOf->types->add($type1);
        $anyOf->types->add($type2);
        $internalGenerator1 = $this->givenValueGeneratorLocator_getValueGenerator_withType_returnsValueGenerator($type1);
        $internalGenerator2 = $this->givenValueGeneratorLocator_getValueGenerator_withType_returnsValueGenerator($type2);
        $this->givenValueGenerator_generateValue_returnsValue($internalGenerator1, self::GENERATED_VALUE_1);
        $this->givenValueGenerator_generateValue_returnsValue($internalGenerator2, self::GENERATED_VALUE_2);

        $value = $generator->generateValue($anyOf);

        $this->assertArraySubset(self::COMMON_PROPERTY_PAIR, $value);
        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledAtMostOnceWithType($type1);
        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledAtMostOnceWithType($type2);
        $this->assertExpectedValueGenerator_generateValue_wasCalledAtMostOnceWithType($internalGenerator1, $type1);
        $this->assertExpectedValueGenerator_generateValue_wasCalledAtMostOnceWithType($internalGenerator2, $type2);
    }

    /**
     * @test
     * @dataProvider possibleExpectedValuesProvider
     */
    public function generateValue_anyOfWithTwoTypes_anyOfValuesRandomlyGeneratedAndMergedAndReturned(array $expectedValue): void
    {
        $generator = new AnyOfValueGenerator($this->valueGeneratorLocator);
        $anyOf = new AnyOfType();
        $type1 = \Phake::mock(TypeMarkerInterface::class);
        $type2 = \Phake::mock(TypeMarkerInterface::class);
        $anyOf->types->add($type1);
        $anyOf->types->add($type2);
        $internalGenerator1 = $this->givenValueGeneratorLocator_getValueGenerator_withType_returnsValueGenerator($type1);
        $internalGenerator2 = $this->givenValueGeneratorLocator_getValueGenerator_withType_returnsValueGenerator($type2);
        $this->givenValueGenerator_generateValue_returnsValue($internalGenerator1, self::GENERATED_VALUE_1);
        $this->givenValueGenerator_generateValue_returnsValue($internalGenerator2, self::GENERATED_VALUE_2);

        $actualValueEqualsExpectedValue = false;
        for ($i = 0; $i < 100; $i++) {
            $value = $generator->generateValue($anyOf);
            $actualValueEqualsExpectedValue = $value === $expectedValue;

            if ($actualValueEqualsExpectedValue) {
                break;
            }
        }

        $this->assertTrue($actualValueEqualsExpectedValue);
    }

    public function possibleExpectedValuesProvider(): array
    {
        return [
            [self::GENERATED_VALUE_1],
            [self::GENERATED_VALUE_2],
            [self::MERGED_GENERATED_VALUE],
        ];
    }
}
