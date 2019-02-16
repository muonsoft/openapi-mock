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

use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\Parsing\ParserInterface;
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
    /** @var ParserInterface */
    protected $internalParser;

    /** @var TypeParserLocator */
    protected $typeParserLocator;

    /** @var ReferenceResolvingParser */
    protected $resolvingParser;

    /** @var ParsingErrorHandlerInterface */
    protected $errorHandler;

    protected function setUpParsingContext(): void
    {
        $this->internalParser = \Phake::mock(ParserInterface::class);
        $this->typeParserLocator = \Phake::mock(TypeParserLocator::class);
        $this->resolvingParser = \Phake::mock(ReferenceResolvingParser::class);
        $this->errorHandler = \Phake::mock(ParsingErrorHandlerInterface::class);
    }

    protected function assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
        SpecificationAccessor $specification,
        array $path
    ): void {
        /* @var SpecificationPointer $pointer */
        \Phake::verify($this->internalParser)
            ->parsePointedSchema($specification, \Phake::capture($pointer));
        Assert::assertSame($path, $pointer->getPathElements());
    }

    protected function assertInternalParser_parsePointedSchema_wasCalledTwiceWithSpecificationAndPointerPaths(
        SpecificationAccessor $specification,
        array $firstPath,
        array $secondPath
    ): void {
        /* @var SpecificationPointer[] $pointers */
        \Phake::verify($this->internalParser, \Phake::times(2))
            ->parsePointedSchema($specification, \Phake::captureAll($pointers));
        Assert::assertSame($firstPath, $pointers[0]->getPathElements());
        Assert::assertSame($secondPath, $pointers[1]->getPathElements());
    }

    protected function assertInternalParser_parsePointedSchema_wasNeverCalledWithAnyParameters(): void
    {
        \Phake::verify($this->internalParser, \Phake::never())
            ->parsePointedSchema(\Phake::anyParameters());
    }

    protected function assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointer(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer
    ): void {
        \Phake::verify($this->internalParser)
            ->parsePointedSchema($specification, $pointer);
    }

    protected function givenInternalParser_parsePointedSchema_returns(SpecificationObjectMarkerInterface ...$objects): void
    {
        $parser = \Phake::when($this->internalParser)->parsePointedSchema(\Phake::anyParameters());

        foreach ($objects as $object) {
            $parser = $parser->thenReturn($object);
        }
    }

    protected function givenInternalParser_parsePointedSchema_returnsObject(): SpecificationObjectMarkerInterface
    {
        $object = \Phake::mock(SpecificationObjectMarkerInterface::class);
        $this->givenInternalParser_parsePointedSchema_returns($object);

        return $object;
    }

    protected function assertTypeParserLocator_getTypeParser_wasCalledOnceWithType(string $type): void
    {
        \Phake::verify($this->typeParserLocator)
            ->getTypeParser($type);
    }

    protected function givenTypeParserLocator_getTypeParser_returnsInternalParser(): void
    {
        \Phake::when($this->typeParserLocator)
            ->getTypeParser(\Phake::anyParameters())
            ->thenReturn($this->internalParser);
    }

    protected function assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndInternalParser(
        SpecificationAccessor $specification,
        array $path
    ): void {
        /* @var SpecificationPointer $pointer */
        \Phake::verify($this->resolvingParser)
            ->resolveReferenceAndParsePointedSchema($specification, \Phake::capture($pointer), $this->internalParser);
        Assert::assertSame($path, $pointer->getPathElements());
    }

    protected function assertReferenceResolvingParser_resolveReferenceAndParsePointedSchema_wasCalledOnceWithSpecificationAndPointerAndInternalParser(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer
    ): void {
        \Phake::verify($this->resolvingParser)
            ->resolveReferenceAndParsePointedSchema($specification, $pointer, $this->internalParser);
    }

    protected function givenReferenceResolvingParser_resolveReferenceAndParsePointedSchema_returns(SpecificationObjectMarkerInterface $object): void
    {
        \Phake::when($this->resolvingParser)
            ->resolveReferenceAndParsePointedSchema(\Phake::anyParameters())
            ->thenReturn($object);
    }

    protected function assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath(string $message, string $path): void
    {
        /* @var SpecificationPointer $pointer */
        \Phake::verify($this->errorHandler)
            ->reportError($message, \Phake::capture($pointer));
        Assert::assertSame($path, $pointer->getPath());
    }

    protected function assertParsingErrorHandler_reportWarning_wasCalledOnceWithMessageAndPointerPath(string $message, string $path): void
    {
        /* @var SpecificationPointer $pointer */
        \Phake::verify($this->errorHandler)
            ->reportWarning($message, \Phake::capture($pointer));
        Assert::assertSame($path, $pointer->getPath());
    }

    protected function givenParsingErrorHandler_reportError_returnsMessage(): string
    {
        $message = 'report message';

        /* @var SpecificationPointer $pointer */
        \Phake::when($this->errorHandler)
            ->reportError(\Phake::anyParameters())
            ->thenReturn($message);

        return $message;
    }

    protected function givenParsingErrorHandler_reportWarning_returnsMessage(): string
    {
        $message = 'report message';

        /* @var SpecificationPointer $pointer */
        \Phake::when($this->errorHandler)
            ->reportWarning(\Phake::anyParameters())
            ->thenReturn($message);

        return $message;
    }
}
