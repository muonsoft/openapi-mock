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
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class FreeFormObjectValueGeneratorTest extends TestCase
{
    use FakerCaseTrait;

    private const DEFAULT_MIN_PROPERTIES = 1;
    private const DEFAULT_MAX_PROPERTIES = 20;
    private const PROPERTIES_COUNT = 5;

    protected function setUp(): void
    {
        $this->setUpFaker();
    }

    /** @test */
    public function generateValue_freeFormObjectType_objectWithRandomKeysAndValuesGeneratedAndReturned(): void
    {
        $type = new FreeFormObjectType();
        $generator = new FreeFormObjectValueGenerator($this->faker);
        $this->givenFaker_method_returnsValue('word', 'value');
        $faker = $this->givenFaker_method_returnsNewFaker('unique');
        $this->givenFakerMock_method_returnsValue($faker, 'word', 'key');

        $value = $generator->generateValue($type);

        $this->assertGreaterThanOrEqual(self::DEFAULT_MIN_PROPERTIES, \count($value));
        $this->assertLessThanOrEqual(self::DEFAULT_MAX_PROPERTIES, \count($value));
        $this->assertArraySubset(['key' => 'value'], $value);
        $this->assertFaker_method_wasCalledAtLeastOnce('unique');
        $this->assertFaker_method_wasCalledAtLeastOnce('word');
        $this->assertFakerMock_method_wasCalledAtLeastOnce($faker, 'word');
    }

    /** @test */
    public function generateValue_freeFormObjectTypeWithFixedCountOfProperties_objectWithPropertiesCountReturned(): void
    {
        $type = new FreeFormObjectType();
        $type->minProperties = self::PROPERTIES_COUNT;
        $type->maxProperties = self::PROPERTIES_COUNT;
        $generator = new FreeFormObjectValueGenerator(Factory::create());

        $value = $generator->generateValue($type);

        $this->assertCount(self::PROPERTIES_COUNT, $value);
    }
}
