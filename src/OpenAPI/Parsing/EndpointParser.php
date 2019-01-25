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

use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockResponse;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $responseParser;

    /** @var ReferenceResolvingParser */
    private $resolvingParser;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ContextualParserInterface $responseParser,
        ReferenceResolvingParser $resolvingParser,
        LoggerInterface $logger
    ) {
        $this->responseParser = $responseParser;
        $this->resolvingParser = $resolvingParser;
        $this->logger = $logger;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $mockParameters = new MockParameters();
        $schema = $specification->getSchema($pointer);

        if (array_key_exists('responses', $schema)) {
            $responsesPointer = $pointer->withPathElement('responses');
            foreach ($schema['responses'] as $statusCode => $responseSpecification) {
                $responsePointer = $responsesPointer->withPathElement($statusCode);
                $this->validateResponse($statusCode, $responseSpecification, $responsePointer);

                /** @var MockResponse $response */
                $response = $this->resolvingParser->resolveReferenceAndParsePointedSchema($specification, $responsePointer, $this->responseParser);
                $parsedStatusCode = $this->parseStatusCode($statusCode);
                $response->statusCode = $parsedStatusCode;
                $mockParameters->responses->set($parsedStatusCode, $response);

                $this->logger->debug(
                    sprintf('Response with status code "%s" was parsed.', $response->statusCode),
                    ['path' => $responsePointer->getPath()]
                );
            }
        }

        return $mockParameters;
    }

    private function validateResponse($statusCode, $responseSpecification, SpecificationPointer $pointer): void
    {
        if (!\is_int($statusCode) && 'default' !== $statusCode) {
            throw new ParsingException('Invalid status code. Must be integer or "default".', $pointer);
        }
        if (!\is_array($responseSpecification)) {
            throw new ParsingException('Invalid response specification.', $pointer);
        }
    }

    private function parseStatusCode($statusCode): int
    {
        $parsedStatusCode = (int) $statusCode;

        if (0 === $parsedStatusCode) {
            $parsedStatusCode = MockResponse::DEFAULT_STATUS_CODE;
        }

        return $parsedStatusCode;
    }
}
