<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Utility\TestCase;

use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\Parsing\TypeParserLocator;
use App\Tests\Unit\OpenAPI\Parsing\SchemaParserTest;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait TypeParserTestCaseTrait
{
    /** @var TypeParserInterface */
    protected $typeParser;

    /** @var TypeParserLocator */
    protected $typeParserLocator;

    protected function setUpTypeParser(): void
    {
        $this->typeParserLocator = \Phake::mock(TypeParserLocator::class);
        $this->typeParser = \Phake::mock(TypeParserInterface::class);
    }

    protected function assertTypeParserLocator_getTypeParser_isCalledOnceWithType(string $type): void
    {
        \Phake::verify($this->typeParserLocator)
            ->getTypeParser($type);
    }

    protected function assertTypeParser_parseTypeSchema_isCalledOnceWithSchema(array $schema): void
    {
        \Phake::verify($this->typeParser)
            ->parseTypeSchema($schema);
    }

    protected function givenTypeParserLocator_getTypeParser_returnsTypeParser(): void
    {
        \Phake::when($this->typeParserLocator)
            ->getTypeParser(\Phake::anyParameters())
            ->thenReturn($this->typeParser);
    }

    protected function givenTypeParser_parseTypeSchema_returnsType(): TypeMarkerInterface
    {
        $type = \Phake::mock(TypeMarkerInterface::class);

        \Phake::when($this->typeParser)
            ->parseTypeSchema(\Phake::anyParameters())
            ->thenReturn($type);

        return $type;
    }
}
