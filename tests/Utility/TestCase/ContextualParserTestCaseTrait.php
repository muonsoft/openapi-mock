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
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\SpecificationObjectMarkerInterface;
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

    protected function assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
        SpecificationAccessor $specification,
        array $path
    ): void {
        /** @var SpecificationPointer $pointer */
        \Phake::verify($this->contextualParser)
            ->parsePointedSchema($specification, \Phake::capture($pointer));
        Assert::assertSame($path, $pointer->getPathElements());
    }

    protected function assertContextualParser_parsePointedSchema_wasNeverCalledWithAnyParameters(): void
    {
        \Phake::verify($this->contextualParser, \Phake::never())
            ->parsePointedSchema(\Phake::anyParameters());
    }

    protected function assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointer(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer
    ): void {
        \Phake::verify($this->contextualParser)
            ->parsePointedSchema($specification, $pointer);
    }

    protected function givenContextualParser_parsePointedSchema_returns(SpecificationObjectMarkerInterface $object): void
    {
        \Phake::when($this->contextualParser)
            ->parsePointedSchema(\Phake::anyParameters())
            ->thenReturn($object);
    }

    protected function givenContextualParser_parsePointedSchema_returnsObject(): SpecificationObjectMarkerInterface
    {
        $object = \Phake::mock(SpecificationObjectMarkerInterface::class);
        $this->givenContextualParser_parsePointedSchema_returns($object);

        return $object;
    }
}
