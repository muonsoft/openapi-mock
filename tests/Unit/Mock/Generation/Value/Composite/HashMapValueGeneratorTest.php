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

use App\Mock\Generation\Value\Composite\HashMapValueGenerator;
use App\Mock\Parameters\Schema\Type\Composite\HashMapType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\FakerCaseTrait;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use App\Utility\StringList;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class HashMapValueGeneratorTest extends TestCase
{
    use FakerCaseTrait;
    use ValueGeneratorCaseTrait;
    use ProbabilityTestCaseTrait;

    private const PROPERTIES_COUNT = 5;

    protected function setUp(): void
    {
        $this->setUpFaker();
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateValue_hashMap_hashMapOfTypedItemsGeneratedAndReturned(): void
    {
        $type = new HashMapType();
        $type->value = new DummyType();
        $generator = $this->createHashMapValueGenerator();
        $this->givenLengthGeneratorGeneratesLength(1);
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $hashMapValue = $this->givenValueGenerator_generateValue_returnsGeneratedValue();
        $faker = $this->givenFaker_method_returnsNewFaker('unique');
        $this->givenFakerMock_method_returnsValue($faker, 'word', 'key');

        $hashMap = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type->value);
        $this->assertValueGenerator_generateValue_wasCalledAtLeastOnceWithType($type->value);
        $this->assertCount(1, get_object_vars($hashMap));
        $this->assertFaker_method_wasCalledAtLeastOnce('unique');
        $this->assertFakerMock_method_wasCalledAtLeastOnce($faker, 'word');
        $this->assertObjectHasAttribute('key', $hashMap);
        $this->assertSame($hashMapValue, $hashMap->key);
        $this->assertLengthGeneratedInRange(0, 0);
    }

    /** @test */
    public function generateValue_hashMapWithFixedCountOfProperties_hashMapWithPropertiesCountReturned(): void
    {
        $type = new HashMapType();
        $type->value = new DummyType();
        $type->minProperties = self::PROPERTIES_COUNT;
        $type->maxProperties = self::PROPERTIES_COUNT;
        $this->givenLengthGeneratorGeneratesLength(self::PROPERTIES_COUNT);
        $generator = $this->createHashMapValueGenerator(Factory::create());
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $hashMap = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type->value);
        $this->assertValueGenerator_generateValue_wasCalledAtLeastOnceWithType($type->value);
        $this->assertCount(self::PROPERTIES_COUNT, get_object_vars($hashMap));
        $this->assertLengthGeneratedInRange(self::PROPERTIES_COUNT, self::PROPERTIES_COUNT);
    }

    /** @test */
    public function generateValue_hashMapWithDefaultProperty_hashMapWithDefaultPropertyReturned(): void
    {
        $type = new HashMapType();
        $type->value = new DummyType();
        $type->minProperties = self::PROPERTIES_COUNT;
        $type->maxProperties = self::PROPERTIES_COUNT;
        $type->required = new StringList(['default']);
        $defaultValueType = \Phake::mock(TypeInterface::class);
        $type->properties->put('default', $defaultValueType);
        $this->givenLengthGeneratorGeneratesLength(self::PROPERTIES_COUNT);
        $generator = $this->createHashMapValueGenerator(Factory::create());
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $value = $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $hashMap = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type->value);
        $this->assertValueGenerator_generateValue_wasCalledAtLeastOnceWithType($type->value);
        $this->assertValueGenerator_generateValue_wasCalledAtLeastOnceWithType($defaultValueType);
        $this->assertLengthGeneratedInRange(self::PROPERTIES_COUNT, self::PROPERTIES_COUNT);
        $this->assertCount(self::PROPERTIES_COUNT, get_object_vars($hashMap));
        $this->assertObjectHasAttribute('default', $hashMap);
        $this->assertSame($value, $hashMap->default);
    }

    /** @test */
    public function generateValue_hashMapTypeIsNullable_nullReturned(): void
    {
        $type = new HashMapType();
        $type->setNullable(true);
        $type->value = new DummyType();
        $type->minProperties = self::PROPERTIES_COUNT;
        $type->maxProperties = self::PROPERTIES_COUNT;
        $generator = $this->createHashMapValueGenerator(Factory::create());
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $test = function () use ($generator, $type) {
            return $generator->generateValue($type);
        };

        $this->expectClosureOccasionallyReturnsNull($test);
    }

    private function createHashMapValueGenerator(Generator $generator = null): HashMapValueGenerator
    {
        return new HashMapValueGenerator(
            $generator ?? $this->faker,
            $this->lengthGenerator,
            $this->valueGeneratorLocator
        );
    }
}
