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

use App\Mock\Parameters\MockParameters;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationParser;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

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
    public function parseSpecification_validSpecification_specificationParsedToMockParameters(): void
    {
        $parser = $this->createSpecificationParser();
        $expectedMockParameters = new MockParameters();
        $this->givenContextualParser_parsePointedSchema_returns($expectedMockParameters);
        $specification = new SpecificationAccessor(self::VALID_SPECIFICATION);

        $mockParametersCollection = $parser->parseSpecification($specification);

        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            ['paths', self::PATH, self::HTTP_METHOD]
        );
        $this->assertCount(1, $mockParametersCollection);
        /** @var MockParameters $mockParameters */
        $mockParameters = $mockParametersCollection->first();
        $this->assertSame($expectedMockParameters, $mockParameters);
        $this->assertSame(self::PATH, $mockParameters->path);
        $this->assertSame(strtoupper(self::HTTP_METHOD), $mockParameters->httpMethod);
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

    /** @test */
    public function parseSpecification_noEndpoints_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();
        $specification = new SpecificationAccessor([
            'openapi' => '3.0',
            'paths'   => [
                '/entity' => 'invalid',
            ],
        ]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Empty or invalid endpoint specification');

        $parser->parseSpecification($specification);
    }

    /** @test */
    public function parseSpecification_invalidEndpoint_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();
        $specification = new SpecificationAccessor([
            'openapi' => '3.0',
            'paths'   => [
                '/entity' => [
                    'get' => 'invalid',
                ],
            ],
        ]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Empty or invalid endpoint specification');

        $parser->parseSpecification($specification);
    }

    /** @test */
    public function parseSpecification_pathWithReference_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();
        $specification = new SpecificationAccessor([
            'openapi' => '3.0',
            'paths'   => [
                '/entity' => [
                    '$ref' => 'anything',
                ],
            ],
        ]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('References on paths is not supported');

        $parser->parseSpecification($specification);
    }

    private function createSpecificationParser(): SpecificationParser
    {
        return new SpecificationParser($this->contextualParser, new NullLogger());
    }
}
