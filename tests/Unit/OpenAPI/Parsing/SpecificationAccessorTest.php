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

use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use PHPUnit\Framework\TestCase;

class SpecificationAccessorTest extends TestCase
{
    private const SPECIFICATION = ['topLevel' => self::TOP_LEVEL_VALUE];
    private const TOP_LEVEL_VALUE = ['midLevel' => self::MID_LEVEL_VALUE];
    private const MID_LEVEL_VALUE = ['lowLevel' => self::LOW_LEVEL_VALUE];
    private const LOW_LEVEL_VALUE = ['value'];
    private const REFERENCE = '#reference';

    /**
     * @test
     * @dataProvider pointerAndExpectedSchemaProvider
     */
    public function getSchema_oneLevelPointerAndPathExist_schemaReturned(SpecificationPointer $pointer, array $expectedSchema): void
    {
        $accessor = new SpecificationAccessor(self::SPECIFICATION);

        $schema = $accessor->getSchema($pointer);

        $this->assertSame($expectedSchema, $schema);
    }

    public function pointerAndExpectedSchemaProvider(): array
    {
        return [
            [new SpecificationPointer(), self::SPECIFICATION],
            [new SpecificationPointer(['topLevel']), self::TOP_LEVEL_VALUE],
            [new SpecificationPointer(['topLevel', 'midLevel']), self::MID_LEVEL_VALUE],
            [new SpecificationPointer(['topLevel', 'midLevel', 'lowLevel']), self::LOW_LEVEL_VALUE],
            [new SpecificationPointer(['emptyPath']), []],
        ];
    }

    /** @test */
    public function getSchema_notAnArraySchema_exceptionThrown(): void
    {
        $accessor = new SpecificationAccessor(['schema' => 'invalid']);
        $pointer = new SpecificationPointer(['schema']);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Schema is expected to be an array or an object');

        $accessor->getSchema($pointer);
    }

    /** @test */
    public function findResolvedObject_noReferenceObject_nullReturned(): void
    {
        $accessor = new SpecificationAccessor([]);

        $object = $accessor->findResolvedObject(self::REFERENCE);

        $this->assertNull($object);
    }

    /** @test */
    public function findResolvedObject_referenceObjectExist_objectReturned(): void
    {
        $accessor = new SpecificationAccessor([]);
        $expectedObject = \Phake::mock(SpecificationObjectMarkerInterface::class);
        $accessor->setResolvedObject(self::REFERENCE, $expectedObject);

        $object = $accessor->findResolvedObject(self::REFERENCE);

        $this->assertSame($expectedObject, $object);
    }
}
