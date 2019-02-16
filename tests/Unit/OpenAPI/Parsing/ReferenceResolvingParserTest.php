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

use App\Mock\Parameters\Schema\Type\InvalidType;
use App\OpenAPI\Parsing\ReferenceResolvingParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ReferenceResolvingParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const REFERENCE = '#/reference';

    /** @var SpecificationAccessor */
    private $specificationAccessor;

    protected function setUp(): void
    {
        $this->specificationAccessor = \Phake::mock(SpecificationAccessor::class);
        $this->setUpParsingContext();
    }

    /** @test */
    public function resolveReferenceAndParsePointedSchema_schemaIsNotReference_InternalParserParsesSchemaAndReturnsObject(): void
    {
        $resolvingParser = $this->createReferenceResolvingParser();
        $pointer = new SpecificationPointer();
        $this->givenSpecificationAccessor_getSchema_returnsSchema(['schema']);
        $expectedObject = $this->givenInternalParser_parsePointedSchema_returnsObject();

        $object = $resolvingParser->resolveReferenceAndParsePointedSchema($this->specificationAccessor, $pointer, $this->internalParser);

        $this->assertSpecificationAccessor_getSchema_wasCalledOnceWithPointer($pointer);
        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointer($this->specificationAccessor, $pointer);
        $this->assertSame($expectedObject, $object);
    }

    /**
     * @test
     * @dataProvider referenceAndReferencedPointerPath
     */
    public function resolveReferenceAndParsePointedSchema_schemaWithNotResolvedReference_InternalParserParsesReferencedSchemaAndReturnsObject(
        string $reference,
        array $referencedPointerPath
    ): void {
        $resolvingParser = $this->createReferenceResolvingParser();
        $pointer = new SpecificationPointer();
        $this->givenSpecificationAccessor_getSchema_returnsSchema(['$ref' => $reference]);
        $expectedObject = $this->givenInternalParser_parsePointedSchema_returnsObject();

        $object = $resolvingParser->resolveReferenceAndParsePointedSchema($this->specificationAccessor, $pointer, $this->internalParser);

        $this->assertSpecificationAccessor_getSchema_wasCalledOnceWithPointer($pointer);
        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath($this->specificationAccessor, $referencedPointerPath);
        $this->assertSame($expectedObject, $object);
    }

    public function referenceAndReferencedPointerPath(): array
    {
        return [
            [self::REFERENCE, ['reference']],
            ['#/one/two/three', ['one', 'two', 'three']],
            ['#/paths/~1blogs~1{blog_id}~1new~0posts', ['paths', '/blogs/{blog_id}/new~posts']],
        ];
    }

    /** @test */
    public function resolveReferenceAndParsePointedSchema_schemaWithNotResolvedReference_parsedObjectSetToSpecification(): void
    {
        $resolvingParser = $this->createReferenceResolvingParser();
        $pointer = new SpecificationPointer();
        $this->givenSpecificationAccessor_getSchema_returnsSchema(['$ref' => self::REFERENCE]);
        $expectedObject = $this->givenInternalParser_parsePointedSchema_returnsObject();
        $this->givenSpecificationAccessor_findResolvedObject_returnsNull();

        $object = $resolvingParser->resolveReferenceAndParsePointedSchema($this->specificationAccessor, $pointer, $this->internalParser);

        $this->assertSpecificationAccessor_findResolvedObject_wasCalledOnceWithReference(self::REFERENCE);
        $this->assertSpecificationAccessor_setResolvedObject_wasCalledOnceWithReferenceAndObject(self::REFERENCE, $object);
        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath($this->specificationAccessor, ['reference']);
        $this->assertSame($expectedObject, $object);
    }

    /** @test */
    public function resolveReferenceAndParsePointedSchema_schemaWithResolvedReference_objectReturnedFromSpecification(): void
    {
        $resolvingParser = $this->createReferenceResolvingParser();
        $pointer = new SpecificationPointer();
        $this->givenSpecificationAccessor_getSchema_returnsSchema(['$ref' => self::REFERENCE]);
        $expectedObject = $this->givenSpecificationAccessor_findResolvedObject_returnsObject();

        $object = $resolvingParser->resolveReferenceAndParsePointedSchema($this->specificationAccessor, $pointer, $this->internalParser);

        $this->assertSpecificationAccessor_findResolvedObject_wasCalledOnceWithReference(self::REFERENCE);
        $this->assertSpecificationAccessor_setResolvedObject_wasNeverCalledOnceWithAnyParameters();
        $this->assertInternalParser_parsePointedSchema_wasNeverCalledWithAnyParameters();
        $this->assertSame($expectedObject, $object);
    }

    /**
     * @test
     * @dataProvider invalidReferenceAndErrorMessageProvider
     */
    public function resolveReferenceAndParsePointedSchema_schemaWithInvalidReference_exceptionThrown(string $reference, string $errorMessage): void
    {
        $resolvingParser = $this->createReferenceResolvingParser();
        $pointer = new SpecificationPointer();
        $this->givenSpecificationAccessor_getSchema_returnsSchema(['$ref' => $reference]);
        $errorReport = $this->givenParsingErrorHandler_reportError_returnsMessage();

        /** @var InvalidType $object */
        $object = $resolvingParser->resolveReferenceAndParsePointedSchema($this->specificationAccessor, $pointer, $this->internalParser);

        $this->assertInstanceOf(InvalidType::class, $object);
        $this->assertSame($errorReport, $object->getError());
        $this->assertParsingErrorHandler_reportError_wasCalledOnceWithMessageAndPointerPath($errorMessage, '$ref');
    }

    public function invalidReferenceAndErrorMessageProvider(): array
    {
        return [
            ['', 'reference cannot be empty'],
            [' ', 'reference cannot be empty'],
            ['file', 'only local references is supported - reference must start with "#/"'],
        ];
    }

    private function assertSpecificationAccessor_getSchema_wasCalledOnceWithPointer(SpecificationPointer $pointer): void
    {
        \Phake::verify($this->specificationAccessor)
            ->getSchema($pointer);
    }

    private function givenSpecificationAccessor_getSchema_returnsSchema(array $schema): void
    {
        \Phake::when($this->specificationAccessor)
            ->getSchema(\Phake::anyParameters())
            ->thenReturn($schema);
    }

    private function assertSpecificationAccessor_findResolvedObject_wasCalledOnceWithReference(string $reference): void
    {
        \Phake::verify($this->specificationAccessor)
            ->findResolvedObject($reference);
    }

    private function assertSpecificationAccessor_setResolvedObject_wasCalledOnceWithReferenceAndObject(string $reference, SpecificationObjectMarkerInterface $object): void
    {
        \Phake::verify($this->specificationAccessor)
            ->setResolvedObject($reference, $object);
    }

    private function assertSpecificationAccessor_setResolvedObject_wasNeverCalledOnceWithAnyParameters(): void
    {
        \Phake::verify($this->specificationAccessor, \Phake::never())
            ->setResolvedObject(\Phake::anyParameters());
    }

    private function givenSpecificationAccessor_findResolvedObject_returnsNull(): void
    {
        \Phake::when($this->specificationAccessor)
            ->findResolvedObject(\Phake::anyParameters())
            ->thenReturn(null);
    }

    private function givenSpecificationAccessor_findResolvedObject_returnsObject(): SpecificationObjectMarkerInterface
    {
        $object = \Phake::mock(SpecificationObjectMarkerInterface::class);

        \Phake::when($this->specificationAccessor)
            ->findResolvedObject(\Phake::anyParameters())
            ->thenReturn($object);

        return $object;
    }

    private function createReferenceResolvingParser(): ReferenceResolvingParser
    {
        return new ReferenceResolvingParser($this->errorHandler, new NullLogger());
    }
}
