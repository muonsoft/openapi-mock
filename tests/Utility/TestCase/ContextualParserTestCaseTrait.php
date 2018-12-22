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

use App\OpenAPI\Parsing\ContextualParserInterface;
use App\OpenAPI\Parsing\SpecificationPointer;
use PHPUnit\Framework\Assert;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait ContextualParserTestCaseTrait
{
    /** @var ContextualParserInterface */
    protected $contextualParser;

    protected function setUpContextualParser(): void
    {
        $this->contextualParser = \Phake::mock(ContextualParserInterface::class);
    }

    protected function assertContextualParser_parse_isCalledOnceWithSchemaAndContextWithPath(
        array $schema,
        string $path
    ): void {
        /** @var SpecificationPointer $context */
        \Phake::verify($this->contextualParser)
            ->parse($schema, \Phake::capture($context));
        Assert::assertSame($path, $context->getPath());
    }

    protected function givenContextualParser_parse_returns($parsingResult): void
    {
        \Phake::when($this->contextualParser)
            ->parse(\Phake::anyParameters())
            ->thenReturn($parsingResult);
    }
}
