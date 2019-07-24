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

use App\Mock\Generation\Value\Composite\FreeFormObjectValueGenerator;
use App\Mock\Parameters\Schema\Type\Composite\FreeFormObjectType;
use App\Tests\Utility\TestCase\FakerCaseTrait;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class FreeFormObjectValueGeneratorTest extends TestCase
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
    public function generateValue_freeFormObjectType_objectWithRandomKeysAndValuesGeneratedAndReturned(): void
    {
        $type = new FreeFormObjectType();
        $generator = $this->createFreeFormObjectValueGenerator();
        $this->givenFaker_method_returnsValue('word', 'value');
        $faker = $this->givenFaker_method_returnsNewFaker('unique');
        $this->givenFakerMock_method_returnsValue($faker, 'word', 'key');
        $this->givenLengthGeneratorGeneratesLength(1);

        $value = $generator->generateValue($type);

        $this->assertCount(1, get_object_vars($value));
        $this->assertObjectHasAttribute('key', $value);
        $this->assertSame('value', $value->key);
        $this->assertFaker_method_wasCalledAtLeastOnce('unique');
        $this->assertFaker_method_wasCalledAtLeastOnce('word');
        $this->assertFakerMock_method_wasCalledAtLeastOnce($faker, 'word');
        $this->assertLengthGeneratedInRange(0, 0);
    }

    /** @test */
    public function generateValue_freeFormObjectTypeWithFixedCountOfProperties_objectWithPropertiesCountReturned(): void
    {
        $type = new FreeFormObjectType();
        $type->minProperties = self::PROPERTIES_COUNT;
        $type->maxProperties = self::PROPERTIES_COUNT;
        $generator = $this->createFreeFormObjectValueGenerator(Factory::create());
        $this->givenLengthGeneratorGeneratesLength(self::PROPERTIES_COUNT);

        $value = $generator->generateValue($type);

        $this->assertCount(self::PROPERTIES_COUNT, get_object_vars($value));
        $this->assertLengthGeneratedInRange(self::PROPERTIES_COUNT, self::PROPERTIES_COUNT);
    }

    /** @test */
    public function generateValue_freeFormObjectTypeIsNullable_nullReturned(): void
    {
        $type = new FreeFormObjectType();
        $type->setNullable(true);
        $generator = $this->createFreeFormObjectValueGenerator(Factory::create());

        $test = function () use ($generator, $type) {
            return $generator->generateValue($type);
        };

        $this->expectClosureOccasionallyReturnsNull($test);
    }

    private function createFreeFormObjectValueGenerator(Generator $faker = null): FreeFormObjectValueGenerator
    {
        return new FreeFormObjectValueGenerator($faker ?? $this->faker, $this->lengthGenerator);
    }
}
