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
use App\OpenAPI\Parsing\EndpointParser;
use App\OpenAPI\Parsing\SpecificationParser;
use PHPUnit\Framework\TestCase;

class SpecificationParserTest extends TestCase
{
    private const PATH = '/entity';
    private const HTTP_METHOD = 'get';
    private const ENDPOINT_SPECIFICATION = ['endpoint_specification'];
    private const VALID_SPECIFICATION = [
        'openapi' => '3.0',
        'paths' => [
            self::PATH => [
                self::HTTP_METHOD => self::ENDPOINT_SPECIFICATION,
            ],
        ],
    ];

    /** @var EndpointParser */
    private $endpointParser;

    protected function setUp(): void
    {
        $this->endpointParser = \Phake::mock(EndpointParser::class);
    }

    /** @test */
    public function parseSpecification_validSpecification_specificationParsedToMockParameters(): void
    {
        $parser = $this->createSpecificationParser();
        $mockParameters = $this->givenEndpointParser_parseEndpoint_returnsMockParameters();

        $mockParametersCollection = $parser->parseSpecification(self::VALID_SPECIFICATION);

        $this->assertEndpointParser_parseEndpoint_isCalledOnceWithEndpointSpecification(self::ENDPOINT_SPECIFICATION);
        $this->assertCount(1, $mockParametersCollection);
        $this->assertSame($mockParameters, $mockParametersCollection->first());
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Cannot detect OpenAPI specification version: tag "openapi" does not exist
     */
    public function parseSpecification_noVersionTag_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();

        $parser->parseSpecification([]);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage OpenAPI specification version is not supported. Supports only 3.*.
     */
    public function parseSpecification_invalidVersionTag_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();

        $parser->parseSpecification([
            'openapi' => '2.0'
        ]);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Section "paths" is empty or does not exist
     */
    public function parseSpecification_noPaths_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();

        $parser->parseSpecification([
            'openapi' => '3.0',
            'paths' => [],
        ]);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid endpoint specification at path
     */
    public function parseSpecification_noEndpoints_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();

        $parser->parseSpecification([
            'openapi' => '3.0',
            'paths' => [
                '/entity' => 'invalid'
            ],
        ]);
    }

    /**
     * @test
     * @expectedException \App\OpenAPI\Parsing\ParsingException
     * @expectedExceptionMessage Invalid endpoint specification at path
     */
    public function parseSpecification_invalidEndpoint_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();

        $parser->parseSpecification([
            'openapi' => '3.0',
            'paths' => [
                '/entity' => [
                    'get' => 'invalid'
                ],
            ],
        ]);
    }

    private function createSpecificationParser(): SpecificationParser
    {
        return new SpecificationParser($this->endpointParser);
    }

    private function assertEndpointParser_parseEndpoint_isCalledOnceWithEndpointSpecification(
        array $specification
    ): void {
        \Phake::verify($this->endpointParser)
            ->parseEndpoint($specification);
    }

    private function givenEndpointParser_parseEndpoint_returnsMockParameters(): MockParameters
    {
        $mockParameters = new MockParameters();

        \Phake::when($this->endpointParser)
            ->parseEndpoint(\Phake::anyParameters())
            ->thenReturn($mockParameters);

        return $mockParameters;
    }
}
