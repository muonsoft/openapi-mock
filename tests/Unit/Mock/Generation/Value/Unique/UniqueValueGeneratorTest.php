<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Unique;

use App\Mock\Generation\Value\Unique\UniqueValueGenerator;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use App\Tests\Utility\TestCase\ValueGeneratorCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UniqueValueGeneratorTest extends TestCase
{
    use ValueGeneratorCaseTrait;

    private const VALUE = 'value';

    protected function setUp(): void
    {
        $this->setUpValueGenerator();
    }

    /** @test */
    public function nextValue_firstStringValue_valueReturnedAndAttemptsNotExceedingLimit(): void
    {
        $type = \Phake::mock(TypeInterface::class);
        $generator = new UniqueValueGenerator($this->valueGenerator, $type);
        $this->givenValueGenerator_generateValue_returnsValue($this->valueGenerator, self::VALUE);

        $value = $generator->nextValue();

        $this->assertValueGenerator_generateValue_wasCalledOnceWithType($type);
        $this->assertSame(self::VALUE, $value);
        $this->assertFalse($generator->isAttemptsExceedingLimit());
    }

    /** @test */
    public function nextValue_secondStringValue_valueReturnedAndAttemptsExceedingLimit(): void
    {
        $type = \Phake::mock(TypeInterface::class);
        $generator = new UniqueValueGenerator($this->valueGenerator, $type);
        $this->givenValueGenerator_generateValue_returnsValue($this->valueGenerator, self::VALUE);

        $generator->nextValue();
        $value = $generator->nextValue();

        $this->assertValueGenerator_generateValue_wasCalledTimesWithType(101, $type);
        $this->assertSame(self::VALUE, $value);
        $this->assertTrue($generator->isAttemptsExceedingLimit());
    }

    /** @test */
    public function nextValue_secondObjectValue_valueReturnedAndAttemptsExceedingLimit(): void
    {
        $type = \Phake::mock(TypeInterface::class);
        $valueGenerator = $this->givenObjectValueGenerator();
        $generator = new UniqueValueGenerator($valueGenerator, $type);

        $generator->nextValue();
        $generator->nextValue();

        $this->assertTrue($generator->isAttemptsExceedingLimit());
    }

    private function givenObjectValueGenerator(): ValueGeneratorInterface
    {
        return new class() implements ValueGeneratorInterface {
            public function generateValue(TypeInterface $type): object
            {
                $object = new \stdClass();
                $object->key = 'value';

                return $object;
            }
        };
    }
}
