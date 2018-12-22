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

use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use PHPUnit\Framework\TestCase;

class SpecificationAccessorTest extends TestCase
{
    private const SPECIFICATION = ['topLevel' => self::TOP_LEVEL_VALUE];
    private const TOP_LEVEL_VALUE = ['midLevel' => self::MID_LEVEL_VALUE];
    private const MID_LEVEL_VALUE = ['lowLevel' => self::LOW_LEVEL_VALUE];
    private const LOW_LEVEL_VALUE = ['value'];

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
            [$this->createPointer(), self::SPECIFICATION],
            [$this->createPointer(['topLevel']), self::TOP_LEVEL_VALUE],
            [$this->createPointer(['topLevel', 'midLevel']), self::MID_LEVEL_VALUE],
            [$this->createPointer(['topLevel', 'midLevel', 'lowLevel']), self::LOW_LEVEL_VALUE],
            [$this->createPointer(['emptyPath']), []],
        ];
    }

    private function createPointer(array $pathElements = []): SpecificationPointer
    {
        $pointer = new SpecificationPointer();

        foreach ($pathElements as $pathElement) {
            $pointer = $pointer->withSubPath($pathElement);
        }

        return $pointer;
    }
}
