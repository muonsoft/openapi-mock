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

use App\Mock\Generation\Value\Unique\UniqueValueGeneratorFactory;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UniqueValueGeneratorFactoryTest extends TestCase
{
    /** @test */
    public function createGenerator_valueGeneratorAndType_uniqueValueGeneratorCreated(): void
    {
        $factory = new UniqueValueGeneratorFactory();
        $valueGenerator = \Phake::mock(ValueGeneratorInterface::class);
        $type = \Phake::mock(TypeInterface::class);

        $uniqueGenerator = $factory->createGenerator($valueGenerator, $type);

        $this->assertNotNull($uniqueGenerator);
    }
}
