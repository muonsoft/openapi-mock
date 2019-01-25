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
use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\Tests\Utility\Dummy\DummyType;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class DataGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function generateData_valueTypeInSchema_valueGeneratedByConcreteGeneratorAndReturned(): void
    {
        $generator = new DataGenerator($this->valueGeneratorLocator, new NullLogger());
        $type = new DummyType();
        $schema = $this->givenSchemaWithValueType($type);
        $generatedValue = $this->givenValueGenerator_generateValue_returnsGeneratedValue();
        $this->givenValueGeneratorLocator_getValueGenerator_returnsValueGenerator();

        $value = $generator->generateData($schema);

        $this->assertValueGeneratorLocator_getValueGenerator_wasCalledOnceWithType($type);
        $this->assertValueGenerator_generateValue_wasCalledOnceWithType($type);
        $this->assertSame($generatedValue, $value);
    }

    private function givenSchemaWithValueType(TypeInterface $type): Schema
    {
        $schema = new Schema();
        $schema->value = $type;

        return $schema;
    }
}
