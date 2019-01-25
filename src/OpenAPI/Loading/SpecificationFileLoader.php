<?php
/*
 * This file is part of ImgCache.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Loading;

use App\Mock\Parameters\MockParametersCollection;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationParser;
use App\OpenAPI\SpecificationLoaderInterface;
use App\Utility\UriLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SpecificationFileLoader implements SpecificationLoaderInterface
{
    private const FORMAT_BY_EXTENSION_MAP = [
        'yaml' => 'yaml',
        'yml' => 'yaml',
        'json' => 'json',
    ];

    /** @var UriLoader */
    private $uriLoader;

    /** @var DecoderInterface */
    private $decoder;

    /** @var SpecificationParser */
    private $parser;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        UriLoader $uriLoader,
        DecoderInterface $decoder,
        SpecificationParser $parser,
        LoggerInterface $logger
    ) {
        $this->uriLoader = $uriLoader;
        $this->decoder = $decoder;
        $this->parser = $parser;
        $this->logger = $logger;
    }

    public function loadMockParameters(string $url): MockParametersCollection
    {
        $this->logger->debug(sprintf('Start loading OpenAPI specification from url "%s".', $url));

        $format = $this->guessFormatByExtension($url);
        $fileContents = $this->uriLoader->loadFileContents($url);
        $specificationSchema = $this->decoder->decode($fileContents, $format);
        $specification = new SpecificationAccessor($specificationSchema);
        $parsedSpecification = $this->parser->parseSpecification($specification);

        $this->logger->info(sprintf('OpenAPI specification was loaded and successfully parsed from url "%s".', $url));

        return $parsedSpecification;
    }

    private function guessFormatByExtension(string $url)
    {
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));

        if (array_key_exists($extension, self::FORMAT_BY_EXTENSION_MAP)) {
            $format = self::FORMAT_BY_EXTENSION_MAP[$extension];
        } else {
            throw new \DomainException('Unsupported OpenAPI specification format. Supported formats: YAML and JSON.');
        }

        return $format;
    }
}
