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

use App\Mock\Parameters\EndpointCollection;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationParser;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class SpecificationParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const PATH = '/entity';
    private const HTTP_METHOD = 'get';
    private const ENDPOINT_SPECIFICATION = ['endpoint_specification'];
    private const VALID_SPECIFICATION = [
        'openapi' => '3.0',
        'paths'   => [
            self::PATH => [
                self::HTTP_METHOD => self::ENDPOINT_SPECIFICATION,
            ],
        ],
    ];

    protected function setUp(): void
    {
        $this->setUpParsingContext();
    }

    /** @test */
    public function parseSpecification_validSpecification_specificationParsedToMockEndpoint(): void
    {
        $parser = $this->createSpecificationParser();
        $expectedEndpoints = new EndpointCollection();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoints);
        $specification = new SpecificationAccessor(self::VALID_SPECIFICATION);

        $endpoints = $parser->parseSpecification($specification);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            ['paths']
        );
        $this->assertSame($expectedEndpoints, $endpoints);
    }

    /** @test */
    public function parseSpecification_noVersionTag_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();
        $specification = new SpecificationAccessor([]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Cannot detect OpenAPI specification version: tag "openapi" does not exist');

        $parser->parseSpecification($specification);
    }

    /** @test */
    public function parseSpecification_invalidVersionTag_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();
        $specification = new SpecificationAccessor([
            'openapi' => '2.0',
        ]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('OpenAPI specification version is not supported. Supports only 3.*.');

        $parser->parseSpecification($specification);
    }

    /** @test */
    public function parseSpecification_noPaths_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();
        $specification = new SpecificationAccessor([
            'openapi' => '3.0',
            'paths'   => [],
        ]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Section "paths" is empty or does not exist');

        $parser->parseSpecification($specification);
    }

    private function createSpecificationParser(): SpecificationParser
    {
        return new SpecificationParser($this->contextualParser);
    }
}
