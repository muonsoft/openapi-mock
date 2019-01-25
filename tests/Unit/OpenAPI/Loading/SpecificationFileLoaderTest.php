<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI\Loading;

use App\Mock\Parameters\MockParametersCollection;
use App\OpenAPI\Loading\SpecificationFileLoader;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationParser;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\Utility\UriLoader;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class SpecificationFileLoaderTest extends TestCase
{
    /** @var UriLoader */
    private $uriLoader;

    /** @var DecoderInterface */
    private $decoder;

    /** @var SpecificationParser */
    private $parser;

    protected function setUp(): void
    {
        $this->uriLoader = \Phake::mock(UriLoader::class);
        $this->decoder = \Phake::mock(DecoderInterface::class);
        $this->parser = \Phake::mock(SpecificationParser::class);
    }

    /**
     * @test
     * @dataProvider urlAndFormatProvider
     */
    public function loadMockParameters_fileExistsByUrlAndFormatIsSupported_specificationParsedToMockParameters(
        string $url,
        string $format
    ): void {
        $loader = $this->createSpecificationLoader();
        $fileContents = $this->givenUriLoader_loadFileContents_returnsContents();
        $specification = $this->givenDecoder_decode_returnsRawSpecification();
        $parsedSpecification = $this->givenSpecificationParser_parseSpecification_returnsParsedSpecification();

        $mockParameters = $loader->loadMockParameters($url);

        $this->assertUriLoader_loadFileContents_wasCalledOnceWithUrl($url);
        $this->assertDecoder_decode_wasCalledOnceWithDataAndFormat($fileContents, $format);
        $this->assertSpecificationParser_parseSpecification_wasCalledOnceWithSpecification($specification);
        $this->assertSame($parsedSpecification, $mockParameters);
    }

    public function urlAndFormatProvider(): array
    {
        return [
            ['specification_url.yaml', 'yaml'],
            ['specification_url.YAML', 'yaml'],
            ['specification_url.yml', 'yaml'],
            ['specification_url.json', 'json'],
        ];
    }

    /**
     * @test
     * @expectedException \DomainException
     * @expectedExceptionMessage Unsupported OpenAPI specification format
     */
    public function loadMockParameters_unsupportedFileFormat_exceptionThrown(): void
    {
        $loader = $this->createSpecificationLoader();

        $loader->loadMockParameters('unsupported_url');
    }

    private function assertUriLoader_loadFileContents_wasCalledOnceWithUrl(string $url): void
    {
        \Phake::verify($this->uriLoader)
            ->loadFileContents($url);
    }

    private function assertDecoder_decode_wasCalledOnceWithDataAndFormat(string $fileContents, $format): void
    {
        \Phake::verify($this->decoder)
            ->decode($fileContents, $format);
    }

    private function assertSpecificationParser_parseSpecification_wasCalledOnceWithSpecification(array $specification): void
    {
        /* @var SpecificationAccessor $specificationAccessor */
        \Phake::verify($this->parser)
            ->parseSpecification(\Phake::capture($specificationAccessor));
        $this->assertInstanceOf(SpecificationAccessor::class, $specificationAccessor);
        $schema = $specificationAccessor->getSchema(new SpecificationPointer());
        $this->assertSame($specification, $schema);
    }

    private function givenUriLoader_loadFileContents_returnsContents(): string
    {
        $fileContents = 'specification_raw_contents';

        \Phake::when($this->uriLoader)
            ->loadFileContents(\Phake::anyParameters())
            ->thenReturn($fileContents);

        return $fileContents;
    }

    private function givenDecoder_decode_returnsRawSpecification(): array
    {
        $specification = ['specification_decoded_contents'];

        \Phake::when($this->decoder)
            ->decode(\Phake::anyParameters())
            ->thenReturn($specification);

        return $specification;
    }

    private function givenSpecificationParser_parseSpecification_returnsParsedSpecification(): MockParametersCollection
    {
        $parsedSpecification = new MockParametersCollection();

        \Phake::when($this->parser)
            ->parseSpecification(\Phake::anyParameters())
            ->thenReturn($parsedSpecification);

        return $parsedSpecification;
    }

    private function createSpecificationLoader(): SpecificationFileLoader
    {
        return new SpecificationFileLoader($this->uriLoader, $this->decoder, $this->parser, new NullLogger());
    }
}
