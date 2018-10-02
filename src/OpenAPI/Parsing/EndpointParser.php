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
class EndpointParser
{
    /** @var ResponseParser */
    private $responseParser;

    public function __construct(ResponseParser $responseParser)
    {
        $this->responseParser = $responseParser;
    }

    public function parseEndpoint(array $endpointSpecification): MockParameters
    {
        $mockParameters = new MockParameters();

        if (array_key_exists('responses', $endpointSpecification)) {
            foreach ($endpointSpecification['responses'] as $statusCode => $responseSpecification) {
                $this->validateResponse($statusCode, $responseSpecification);

                $response = $this->responseParser->parseResponse($responseSpecification);
                $response->statusCode = (int) $statusCode;
                $mockParameters->responses->set((int) $statusCode, $response);
            }
        }

        return $mockParameters;
    }

    private function validateResponse($statusCode, $responseSpecification): void
    {
        if (!\is_int($statusCode)) {
            throw new ParsingException('Invalid status code. Must be integer.');
        }
        if (!\is_array($responseSpecification)) {
            throw new ParsingException('Invalid response specification.');
        }
    }
}
