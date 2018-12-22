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

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $responseParser;

    public function __construct(ContextualParserInterface $responseParser)
    {
        $this->responseParser = $responseParser;
    }

    public function parse(array $schema, SpecificationPointer $pointer): MockParameters
    {
        $mockParameters = new MockParameters();

        if (array_key_exists('responses', $schema)) {
            $responsesContext = $pointer->withSubPath('responses');
            foreach ($schema['responses'] as $statusCode => $responseSpecification) {
                $responseContext = $responsesContext->withSubPath($statusCode);
                $this->validateResponse($statusCode, $responseSpecification, $responseContext);

                $response = $this->responseParser->parse($responseSpecification, $responseContext);
                $response->statusCode = (int) $statusCode;
                $mockParameters->responses->set((int) $statusCode, $response);
            }
        }

        return $mockParameters;
    }

    private function validateResponse($statusCode, $responseSpecification, SpecificationPointer $context): void
    {
        if (!\is_int($statusCode)) {
            throw new ParsingException('Invalid status code. Must be integer.', $context);
        }
        if (!\is_array($responseSpecification)) {
            throw new ParsingException('Invalid response specification.', $context);
        }
    }
}
