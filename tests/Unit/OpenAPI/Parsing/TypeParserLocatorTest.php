<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Parsing;

use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\Parsing\TypeParserLocator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class TypeParserLocatorTest extends TestCase
{
    private const TYPE_PARSER_SERVICE_ID = 'type_parser_service_id';

    /** @var ContainerInterface */
    private $container;

    protected function setUp(): void
    {
        $this->container = \Phake::mock(ContainerInterface::class);
    }

    /** @test */
    public function getTypeParser_existingType_typeParserReturned(): void
    {
        $locator = $this->createTypeParserLocator();
        $containerParser = $this->givenContainer_get_returnsTypeParser();

        $parser = $locator->getTypeParser('type');

        $this->assertContainer_get_isCalledOnceWithServiceId(self::TYPE_PARSER_SERVICE_ID);
        $this->assertSame($containerParser, $parser);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Unrecognized schema type
     */
    public function getTypeParser_notExistingType_exceptionThrown(): void
    {
        $locator = $this->createTypeParserLocator();

        $locator->getTypeParser('invalid');
    }

    private function assertContainer_get_isCalledOnceWithServiceId(string $serviceId): void
    {
        \Phake::verify($this->container)
            ->get($serviceId);
    }

    private function givenContainer_get_returnsTypeParser(): TypeParserInterface
    {
        $valueGenerator = \Phake::mock(TypeParserInterface::class);

        \Phake::when($this->container)
            ->get(\Phake::anyParameters())
            ->thenReturn($valueGenerator);

        return $valueGenerator;
    }

    private function createTypeParserLocator(): TypeParserLocator
    {
        return new TypeParserLocator(
            $this->container,
            [
                'type' => self::TYPE_PARSER_SERVICE_ID
            ]
        );
    }
}
