<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock;

use App\Mock\Parameters\Endpoint;
use App\OpenAPI\SpecificationLoaderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointRepository
{
    /** @var SpecificationLoaderInterface */
    private $specificationLoader;

    /** @var string */
    private $specificationUrl;

    public function __construct(SpecificationLoaderInterface $specificationLoader, string $specificationUrl)
    {
        $this->specificationLoader = $specificationLoader;
        $this->specificationUrl = $specificationUrl;
    }

    public function findMockEndpoint(string $httpMethod, string $uri): ?Endpoint
    {
        $foundEndpoint = null;
        $uri = rtrim($uri, '\/');

        $endpoints = $this->specificationLoader->loadMockEndpoints($this->specificationUrl);

        /** @var Endpoint $endpoint */
        foreach ($endpoints as $endpoint) {
            if ($this->endpointMatchesToMethodAndUri($endpoint, $httpMethod, $uri)) {
                $foundEndpoint = $endpoint;

                break;
            }
        }

        return $foundEndpoint;
    }

    private function endpointMatchesToMethodAndUri(Endpoint $endpoint, string $httpMethod, string $uri): bool
    {
        return strtolower($httpMethod) === $endpoint->httpMethod->getValue()
            && $endpoint->urlMatcher->urlIsMatching($uri);
    }
}
