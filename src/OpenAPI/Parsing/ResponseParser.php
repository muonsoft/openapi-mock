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

    public function parse(array $responseSpecification, ParsingContext $context): MockResponse
    {
        $response = new MockResponse();
        $content = $responseSpecification['content'] ?? [];
        $contentContext = $context->withSubPath('content');
        $this->validateContent($content, $contentContext);

        foreach ($content as $mediaType => $schema) {
            $mediaTypeContext = $contentContext->withSubPath($mediaType);
            $parsedSchema = $this->schemaParser->parse($schema, $mediaTypeContext);
            $response->content->set($mediaType, $parsedSchema);
        }

        return $response;
    }

    private function validateContent($content, ParsingContext $context): void
    {
        if (!\is_array($content)) {
            throw new ParsingException('Invalid response content', $context);
        }
    }
}
