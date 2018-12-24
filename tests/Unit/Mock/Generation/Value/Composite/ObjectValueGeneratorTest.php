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

use App\Mock\Generation\Value\Composite\ObjectValueGenerator;
use App\Mock\Parameters\Schema\Type\Composite\ObjectType;
use App\Mock\Parameters\Schema\Type\TypeCollection;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

class ObjectValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    private const PROPERTY_NAME = 'name';

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateValue_objectTypeWithPropertyType_objectWithPropertyGeneratedAndReturned(): void
    {
        $type = new ObjectType();
        $propertyType = new DummyType();
        $type->properties = new TypeCollection([
            self::PROPERTY_NAME => $propertyType,
        ]);
        $generator = new ObjectValueGenerator($this->valueGeneratorLocator);
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();
        $propertyValue = $this->givenValueGenerator_generateValue_returnsGeneratedValue();

        $value = $generator->generateValue($type);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($propertyType);
        $this->assertValueGenerator_generateValue_wasCalledOnceWithType($propertyType);
        $this->assertSame([self::PROPERTY_NAME => $propertyValue], $value);
    }
}
