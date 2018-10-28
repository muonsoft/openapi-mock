<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\DependencyInjection;

use App\DependencyInjection\ValueGeneratorPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ValueGeneratorPassTest extends TestCase
{
    private const VALUE_GENERATOR_TAG = 'app.value_generator';
    private const VALUE_GENERATOR_SERVICE_LOCATOR = 'value_generator_service_locator';
    private const HANDLER_SERVICE_ID = 'handler_service_id';

    /** @var ContainerBuilder */
    private $container;

    protected function setUp(): void
    {
        $this->container = \Phake::mock(ContainerBuilder::class);
    }

    /** @test */
    public function process_containerWithChainHandlerServiceLocator_chainHandlerMapInjectedIntoServiceLocator(): void
    {
        $pass = new ValueGeneratorPass();
        $this->givenContainer_has_returnsTrue();
        $definition = $this->givenContainer_findDefinition_returnsDefinition();
        $this->givenContainer_findTaggedServiceIds_returnsServiceMap([self::HANDLER_SERVICE_ID => '']);

        $pass->process($this->container);

        $this->assertContainer_findDefinition_isCalledOnceWithId(self::VALUE_GENERATOR_SERVICE_LOCATOR);
        $this->assertContainer_findTaggedServiceIds_isCalledOnceWithTag(self::VALUE_GENERATOR_TAG);
        $this->assertDefinitionHasValidValueGeneratorMap($definition);
    }

    private function assertContainer_findDefinition_isCalledOnceWithId(string $id): void
    {
        \Phake::verify($this->container)
            ->findDefinition($id);
    }

    private function givenContainer_has_returnsTrue(): void
    {
        \Phake::when($this->container)
            ->has(\Phake::anyParameters())
            ->thenReturn(true);
    }

    private function assertContainer_findTaggedServiceIds_isCalledOnceWithTag(string $tag): void
    {
        \Phake::verify($this->container)
            ->findTaggedServiceIds($tag);
    }

    private function assertDefinitionHasValidValueGeneratorMap(Definition $definition): void
    {
        \Phake::verify($definition)
            ->addArgument(\Phake::capture($serviceMap));
        $this->assertArrayHasKey(self::HANDLER_SERVICE_ID, $serviceMap);
        $this->assertInstanceOf(Reference::class, $serviceMap[self::HANDLER_SERVICE_ID]);
        $this->assertSame(self::HANDLER_SERVICE_ID, (string) $serviceMap[self::HANDLER_SERVICE_ID]);
    }

    private function givenContainer_findDefinition_returnsDefinition(): Definition
    {
        $definition = \Phake::mock(Definition::class);

        \Phake::when($this->container)
            ->findDefinition(\Phake::anyParameters())
            ->thenReturn($definition);

        return $definition;
    }

    private function givenContainer_findTaggedServiceIds_returnsServiceMap(array $serviceMap): void
    {
        \Phake::when($this->container)
            ->findTaggedServiceIds(\Phake::anyParameters())
            ->thenReturn($serviceMap);
    }
}
