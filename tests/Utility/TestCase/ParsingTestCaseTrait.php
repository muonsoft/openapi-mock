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
use App\OpenAPI\Parsing\ReferenceResolvingParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\Parsing\Type\TypeParserLocator;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use PHPUnit\Framework\Assert;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait ParsingTestCaseTrait
{
    /** @var ContextualParserInterface */
    protected $contextualParser;

    /** @var TypeParserLocator */
    protected $typeParserLocator;

    /** @var ReferenceResolvingParser */
    protected $resolvingParser;

    protected function setUpParsingContext(): void
    {
        $this->contextualParser = \Phake::mock(ContextualParserInterface::class);
        $this->typeParserLocator = \Phake::mock(TypeParserLocator::class);
        $this->resolvingParser = \Phake::mock(ReferenceResolvingParser::class);
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

    protected function assertContextualParser_parsePointedSchema_wasCalledTwiceWithSpecificationAndPointerPaths(
        SpecificationAccessor $specification,
        array $firstPath,
        array $secondPath
    ): void {
        /** @var SpecificationPointer[] $pointers */
        \Phake::verify($this->contextualParser, \Phake::times(2))
            ->parsePointedSchema($specification, \Phake::captureAll($pointers));
        Assert::assertSame($firstPath, $pointers[0]->getPathElements());
        Assert::assertSame($secondPath, $pointers[1]->getPathElements());
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

    protected function assertTypeParserLocator_getTypeParser_wasCalledOnceWithType(string $type): void
    {
        \Phake::verify($this->typeParserLocator)
            ->getTypeParser($type);
    }

    protected function givenTypeParserLocator_getTypeParser_returnsContextualParser(): void
    {
        \Phake::when($this->typeParserLocator)
            ->getTypeParser(\Phake::anyParameters())
            ->thenReturn($this->contextualParser);
    }

    protected function assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContextualParser(
        SpecificationAccessor $specification,
        array $path
    ): void {
        /** @var SpecificationPointer $pointer */
        \Phake::verify($this->resolvingParser)
            ->resolveReferenceAndParsePointedSchema($specification, \Phake::capture($pointer), $this->contextualParser);
        Assert::assertSame($path, $pointer->getPathElements());
    }

    protected function assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerAndContextualParser(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer
    ): void {
        \Phake::verify($this->resolvingParser)
            ->resolveReferenceAndParsePointedSchema($specification, $pointer, $this->contextualParser);
    }

    protected function givenReferenceResolvingParser_resolveReferenceAndParsePointedSchema_returns(SpecificationObjectMarkerInterface $object): void
    {
        \Phake::when($this->resolvingParser)
            ->resolveReferenceAndParsePointedSchema(\Phake::anyParameters())
            ->thenReturn($object);
    }
}
