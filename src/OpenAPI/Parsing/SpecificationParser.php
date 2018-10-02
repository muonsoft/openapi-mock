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

use App\Mock\Parameters\MockParametersCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SpecificationParser
{
    /** @var EndpointParser */
    private $endpointParser;

    public function __construct(EndpointParser $endpointParser)
    {
        $this->endpointParser = $endpointParser;
    }

    public function parseSpecification(array $specification): MockParametersCollection
    {
        $this->validateSpecification($specification);

        $collection = new MockParametersCollection();

        foreach ($specification['paths'] as $path => $endpoints) {
            $this->validateEndpointSpecificationAtPath($endpoints, $path);

            foreach ($endpoints as $httpMethod => $endpointSpecification) {
                $this->validateEndpointSpecificationAtPath($endpointSpecification, $path);

                $mockParameters = $this->endpointParser->parseEndpoint($endpointSpecification);
                $mockParameters->path = $path;
                $mockParameters->httpMethod = strtoupper($httpMethod);
                $collection->add($mockParameters);
            }
        }

        return $collection;
    }

    private function validateSpecification(array $specification): void
    {
        if (!array_key_exists('openapi', $specification)) {
            throw new ParsingException('Cannot detect OpenAPI specification version: tag "openapi" does not exist.');
        }

        if (((int)$specification['openapi']) !== 3) {
            throw new ParsingException('OpenAPI specification version is not supported. Supports only 3.*.');
        }

        if (
            !array_key_exists('paths', $specification)
            || !\is_array($specification['paths'])
            || \count($specification['paths']) === 0
        ) {
            throw new ParsingException('Section "paths" is empty or does not exist');
        }
    }

    private function validateEndpointSpecificationAtPath($endpointSpecification, string $path): void
    {
        if (!\is_array($endpointSpecification) || \count($endpointSpecification) === 0) {
            throw new ParsingException(sprintf('Invalid endpoint specification at path "%s"', $path));
        }
    }
}
