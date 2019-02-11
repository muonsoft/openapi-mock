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
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $schemaParser;

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ContextualParserInterface $schemaParser, ParsingErrorHandlerInterface $errorHandler, LoggerInterface $logger)
    {
        $this->schemaParser = $schemaParser;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $response = new MockResponse();
        $responseSchema = $specification->getSchema($pointer);
        $contentPointer = $pointer->withPathElement('content');
        $mediaTypes = $this->getMediaTypes($responseSchema, $contentPointer);

        foreach ($mediaTypes as $mediaType) {
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

    private function getMediaTypes(array $responseSchema, SpecificationPointer $contentPointer): array
    {
        $mediaTypes = [];
        $content = $responseSchema['content'] ?? [];
        $isValid = $this->validateContent($content, $contentPointer);

        if ($isValid) {
            $mediaTypes = array_keys($content);
        }

        return $mediaTypes;
    }

    private function validateContent($content, SpecificationPointer $pointer): bool
    {
        $isValid = \is_array($content);

        if (!$isValid) {
            $this->errorHandler->reportError('Invalid response content', $pointer);
        }

        return $isValid;
    }
}
