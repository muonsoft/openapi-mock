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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $schemaParser;

    public function __construct(ContextualParserInterface $schemaParser)
    {
        $this->schemaParser = $schemaParser;
    }

    public function parsePointedSchema(array $responseSpecification, SpecificationPointer $pointer): MockResponse
    {
        $response = new MockResponse();
        $content = $responseSpecification['content'] ?? [];
        $contentContext = $pointer->withPathElement('content');
        $this->validateContent($content, $contentContext);

        foreach ($content as $mediaType => $schema) {
            $mediaTypeContext = $contentContext->withPathElement($mediaType);
            $parsedSchema = $this->schemaParser->parsePointedSchema($schema, $mediaTypeContext);
            $response->content->set($mediaType, $parsedSchema);
        }

        return $response;
    }

    private function validateContent($content, SpecificationPointer $context): void
    {
        if (!\is_array($content)) {
            throw new ParsingException('Invalid response content', $context);
        }
    }
}
