<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing;

use App\Mock\Parameters\MockResponse;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $schemaParser;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ContextualParserInterface $schemaParser, LoggerInterface $logger)
    {
        $this->schemaParser = $schemaParser;
        $this->logger = $logger;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $response = new MockResponse();
        $responseSchema = $specification->getSchema($pointer);
        $content = $responseSchema['content'] ?? [];
        $contentPointer = $pointer->withPathElement('content');
        $this->validateContent($content, $contentPointer);

        foreach (array_keys($content) as $mediaType) {
            $mediaTypePointer = $contentPointer->withPathElement($mediaType);
            $parsedSchema = $this->schemaParser->parsePointedSchema($specification, $mediaTypePointer);
            $response->content->set($mediaType, $parsedSchema);

            $this->logger->debug(
                sprintf('Response content scheme for media type "%s" was parsed.', $mediaType),
                ['path' => $mediaTypePointer->getPath()]
            );
        }

        return $response;
    }

    private function validateContent($content, SpecificationPointer $pointer): void
    {
        if (!\is_array($content)) {
            throw new ParsingException('Invalid response content', $pointer);
        }
    }
}
