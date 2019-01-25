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

use App\Mock\Generation\Value\Combined\AllOfValueGenerator;
use App\Mock\Parameters\Schema\Type\Combined\AllOfType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

class AllOfValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    private const GENERATED_VALUE_1 = ['property1' => 'value1'];
    private const GENERATED_VALUE_2 = ['property2' => 'value2'];
    private const MERGED_GENERATED_VALUE = [
        'property1' => 'value1',
        'property2' => 'value2',
    ];

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateValue_allOfWithTwoTypes_allOfValuesGeneratedAndMergedAndReturned(): void
    {
        $generator = new AllOfValueGenerator($this->valueGeneratorLocator);
        $allOf = new AllOfType();
        $type1 = \Phake::mock(TypeInterface::class);
        $type2 = \Phake::mock(TypeInterface::class);
        $allOf->types->add($type1);
        $allOf->types->add($type2);
        $internalGenerator1 = $this->givenValueGeneratorLocator_getValueGenerator_withType_returnsValueGenerator($type1);
        $internalGenerator2 = $this->givenValueGeneratorLocator_getValueGenerator_withType_returnsValueGenerator($type2);
        $this->givenValueGenerator_generateValue_returnsValue($internalGenerator1, self::GENERATED_VALUE_1);
        $this->givenValueGenerator_generateValue_returnsValue($internalGenerator2, self::GENERATED_VALUE_2);

        $value = $generator->generateValue($allOf);

        $this->assertSame(self::MERGED_GENERATED_VALUE, $value);
        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type1);
        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type2);
        $this->assertExpectedValueGenerator_generateValue_wasCalledOnceWithType($internalGenerator1, $type1);
        $this->assertExpectedValueGenerator_generateValue_wasCalledOnceWithType($internalGenerator2, $type2);
    }
}
