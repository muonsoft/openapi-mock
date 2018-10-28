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

use App\DependencyInjection\TypeParserPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TypeParserPassTest extends TestCase
{
    private const TYPE_PARSER_TAG = 'app.type_parser';
    private const TYPE_PARSER_SERVICE_LOCATOR = 'type_parser_service_locator';
    private const HANDLER_SERVICE_ID = 'handler_service_id';

    /** @var ContainerBuilder */
    private $container;

    protected function setUp(): void
    {
        $this->container = \Phake::mock(ContainerBuilder::class);
    }

    /** @test */
    public function process_containerWithTypeParserServiceLocator_typeParserMapInjectedIntoServiceLocator(): void
    {
        $pass = new TypeParserPass();
        $this->givenContainer_has_returnsTrue();
        $definition = $this->givenContainer_findDefinition_returnsDefinition();
        $this->givenContainer_findTaggedServiceIds_returnsServiceMap([self::HANDLER_SERVICE_ID => '']);

        $pass->process($this->container);

        $this->assertContainer_findDefinition_isCalledOnceWithId(self::TYPE_PARSER_SERVICE_LOCATOR);
        $this->assertContainer_findTaggedServiceIds_isCalledOnceWithTag(self::TYPE_PARSER_TAG);
        $this->assertDefinitionHasValidTypeParserMap($definition);
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

    private function assertDefinitionHasValidTypeParserMap(Definition $definition): void
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
