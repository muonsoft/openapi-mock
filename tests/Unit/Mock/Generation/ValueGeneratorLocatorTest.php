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

use App\Mock\Exception\MockGenerationException;
use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Generation\ValueGeneratorLocator;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\Tests\Utility\Dummy\DummyType;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ValueGeneratorLocatorTest extends TestCase
{
    private const VALUE_GENERATOR_SERVICE_ID = 'value_generator_service_id';

    /** @var ContainerInterface */
    private $container;

    protected function setUp(): void
    {
        $this->container = \Phake::mock(ContainerInterface::class);
    }

    /** @test */
    public function getValueGenerator_typeClass_generatorReturned(): void
    {
        $locator = new ValueGeneratorLocator($this->container, [
            DummyType::class => self::VALUE_GENERATOR_SERVICE_ID
        ]);
        $type = new DummyType();
        $containerGenerator = $this->givenContainer_get_returnsValueGenerator();

        $generator = $locator->getValueGenerator($type);

        $this->assertContainer_get_wasCalledOnceWithServiceId(self::VALUE_GENERATOR_SERVICE_ID);
        $this->assertSame($containerGenerator, $generator);
    }

    /** @test */
    public function getValueGenerator_noClassInMap_exceptionThrown(): void
    {
        $locator = new ValueGeneratorLocator($this->container, [
            DummyType::class => self::VALUE_GENERATOR_SERVICE_ID
        ]);
        $type = \Phake::mock(TypeMarkerInterface::class);

        $this->expectException(MockGenerationException::class);
        $this->expectExceptionMessageRegExp('/Value generator for class .* not found/');

        $locator->getValueGenerator($type);
    }

    private function assertContainer_get_wasCalledOnceWithServiceId(string $serviceId): void
    {
        \Phake::verify($this->container)
            ->get($serviceId);
    }

    private function givenContainer_get_returnsValueGenerator(): ValueGeneratorInterface
    {
        $valueGenerator = \Phake::mock(ValueGeneratorInterface::class);

        \Phake::when($this->container)
            ->get(\Phake::anyParameters())
            ->thenReturn($valueGenerator);

        return $valueGenerator;
    }
}
