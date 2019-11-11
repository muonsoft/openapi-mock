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
use App\Mock\Parameters\Servers;
use App\OpenAPI\Parsing\ParsingException;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationContext;
use App\OpenAPI\Parsing\SpecificationParser;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Tests\Utility\TestCase\ParsingTestCaseTrait;
use PHPUnit\Framework\TestCase;

class SpecificationParserTest extends TestCase
{
    use ParsingTestCaseTrait;

    private const PATH = '/entity';
    private const HTTP_METHOD = 'get';
    private const ENDPOINT_SPECIFICATION = ['endpointSpecification'];
    private const SERVERS_SPECIFICATION = ['serversSpecification'];
    private const VALID_SPECIFICATION = [
        'openapi' => '3.0',
        'servers' => self::SERVERS_SPECIFICATION,
        'paths'   => [
            self::PATH => [
                self::HTTP_METHOD => self::ENDPOINT_SPECIFICATION,
            ],
        ],
    ];

    /** @test */
    public function parseSpecification_validSpecificationWithServers_specificationParsedToMockEndpoint(): void
    {
        $parser = $this->createSpecificationParser();
        $servers = new Servers();
        $this->givenInternalParser_parsePointedSchema_returns($servers);
        $expectedEndpoints = new EndpointCollection();
        $this->givenContextualParser_parsePointedSchema_returns($expectedEndpoints);
        $specification = new SpecificationAccessor(self::VALID_SPECIFICATION);

        $endpoints = $parser->parseSpecification($specification);

        /* @var SpecificationPointer $pointer */
        $this->assertInternalParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPath(
            $specification,
            ['servers']
        );
        $this->assertContextualParser_parsePointedSchema_wasCalledOnceWithSpecificationAndPointerPathAndContext(
            $specification,
            ['paths'],
            $specificationContext
        );
        /* @var SpecificationContext $specificationContext */
        $this->assertInstanceOf(SpecificationContext::class, $specificationContext);
        $this->assertSame($servers, $specificationContext->getServers());
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
    public function parseSpecification_swaggerTag_parsingExceptionThrown(): void
    {
        $parser = $this->createSpecificationParser();
        $specification = new SpecificationAccessor([
            'swagger' => '2.0',
        ]);

        $this->expectException(ParsingException::class);
        $this->expectExceptionMessage('Swagger specification is not supported. Supports only OpenAPI v3.*.');

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
        return new SpecificationParser($this->internalParser, $this->contextualParser);
    }
}
