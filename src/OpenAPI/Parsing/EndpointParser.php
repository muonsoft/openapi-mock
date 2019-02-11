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
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
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

    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ContextualParserInterface $responseParser,
        ReferenceResolvingParser $resolvingParser,
        ParsingErrorHandlerInterface $errorHandler,
        LoggerInterface $logger
    ) {
        $this->responseParser = $responseParser;
        $this->resolvingParser = $resolvingParser;
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $mockParameters = new MockParameters();
        $schema = $specification->getSchema($pointer);

        $responses = $schema['responses'] ?? [];
        $responsesPointer = $pointer->withPathElement('responses');

        foreach ($responses as $statusCode => $responseSpecification) {
            $responsePointer = $responsesPointer->withPathElement($statusCode);
            $isValid = $this->validateResponse($statusCode, $responseSpecification, $responsePointer);

            if ($isValid) {
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

    private function validateResponse($statusCode, $responseSpecification, SpecificationPointer $pointer): bool
    {
        $isValid = true;

        if (!\is_int($statusCode) && 'default' !== $statusCode) {
            $isValid = false;
            $this->errorHandler->reportError('Invalid status code. Must be integer or "default".', $pointer);
        }

        if (!\is_array($responseSpecification)) {
            $isValid = false;
            $this->errorHandler->reportError('Invalid response specification.', $pointer);
        }

        return $isValid;
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
