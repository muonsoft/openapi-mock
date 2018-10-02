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
class ResponseParser
{
    /** @var SchemaParser */
    private $schemaParser;

    public function __construct(SchemaParser $schemaParser)
    {
        $this->schemaParser = $schemaParser;
    }

    public function parseResponse(array $responseSpecification): MockResponse
    {
        $response = new MockResponse();
        $content = $responseSpecification['content'] ?? [];
        $this->validateContent($content);

        foreach ($content as $mediaType => $schema) {
            $parsedSchema = $this->schemaParser->parseSchema($schema);
            $response->content->set($mediaType, $parsedSchema);
        }

        return $response;
    }

    private function validateContent($content): void
    {
        if (!\is_array($content)) {
            throw new ParsingException('Invalid response content');
        }
    }
}
