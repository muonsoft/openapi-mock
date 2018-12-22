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
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\TypeParserInterface;
use App\OpenAPI\Parsing\Type\TypeParserLocator;

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

    protected function assertTypeParser_parse_isCalledOnceWithSchemaAndContext(array $schema, SpecificationPointer $context): void
    {
        \Phake::verify($this->typeParser)
            ->parse($schema, $context);
    }

    protected function givenTypeParserLocator_getTypeParser_returnsTypeParser(): void
    {
        \Phake::when($this->typeParserLocator)
            ->getTypeParser(\Phake::anyParameters())
            ->thenReturn($this->typeParser);
    }

    protected function givenTypeParser_parse_returnsType(): TypeMarkerInterface
    {
        $type = \Phake::mock(TypeMarkerInterface::class);

        \Phake::when($this->typeParser)
            ->parse(\Phake::anyParameters())
            ->thenReturn($type);

        return $type;
    }
}
