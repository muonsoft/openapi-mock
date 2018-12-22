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
use App\OpenAPI\Parsing\Type\SchemaTransformingParser;
use PHPUnit\Framework\Assert;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait SchemaTransformingParserTestCase
{
    /** @var SchemaTransformingParser */
    protected $schemaTransformingParser;

    protected function setUpSchemaTransformingParser(): void
    {
        $this->schemaTransformingParser = \Phake::mock(SchemaTransformingParser::class);
    }

    protected function assertSchemaTransformingParser_parse_isCalledOnceWithSchemaAndContextWithPath(
        array $schema,
        string $path
    ): void {
        /** @var SpecificationPointer $context */
        \Phake::verify($this->schemaTransformingParser)
            ->parse($schema, \Phake::capture($context));
        Assert::assertSame($path, $context->getPath());
    }

    protected function givenSchemaTransformingParser_parse_returnsType(): TypeMarkerInterface
    {
        $type = \Phake::mock(TypeMarkerInterface::class);

        \Phake::when($this->schemaTransformingParser)
            ->parse(\Phake::anyParameters())
            ->thenReturn($type);

        return $type;
    }
}
