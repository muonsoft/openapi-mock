<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\OpenAPI;

use App\Mock\Parameters\MockParametersCollection;
use App\OpenAPI\Parsing\SpecificationParser;
use App\OpenAPI\SpecificationLoader;
use App\Utility\FileLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class SpecificationLoaderTest extends TestCase
{
    /** @var FileLoader */
    private $fileLoader;

    /** @var DecoderInterface */
    private $decoder;

    /** @var SpecificationParser */
    private $parser;

    protected function setUp(): void
    {
        $this->fileLoader = \Phake::mock(FileLoader::class);
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
        $fileContents = $this->givenFileLoader_loadFileContents_returnsContents();
        $specification = $this->givenDecoder_decode_returnsRawSpecification();
        $parsedSpecification = $this->givenSpecificationParser_parseSpecification_returnsParsedSpecification();

        $mockParameters = $loader->loadMockParameters($url);

        $this->assertFileLoader_loadFileContents_isCalledOnceWithUrl($url);
        $this->assertDecoder_decode_isCalledOnceWithDataAndFormat($fileContents, $format);
        $this->assertSpecificationParser_parseSpecification_isCalledOnceWithRawSpecification($specification);
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

    private function assertFileLoader_loadFileContents_isCalledOnceWithUrl(string $url): void
    {
        \Phake::verify($this->fileLoader)
            ->loadFileContents($url);
    }

    private function assertDecoder_decode_isCalledOnceWithDataAndFormat(string $fileContents, $format): void
    {
        \Phake::verify($this->decoder)
            ->decode($fileContents, $format);
    }

    private function assertSpecificationParser_parseSpecification_isCalledOnceWithRawSpecification(array $specification): void
    {
        \Phake::verify($this->parser)
            ->parseSpecification($specification);
    }

    private function givenFileLoader_loadFileContents_returnsContents(): string
    {
        $fileContents = 'specification_raw_contents';

        \Phake::when($this->fileLoader)
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

    private function createSpecificationLoader(): SpecificationLoader
    {
        return new SpecificationLoader($this->fileLoader, $this->decoder, $this->parser);
    }
}
