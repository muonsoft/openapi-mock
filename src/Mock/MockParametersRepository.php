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

use App\Mock\Parameters\MockParameters;
use App\OpenAPI\SpecificationLoaderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockParametersRepository
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

    public function findMockParameters(string $httpMethod, string $uri): ?MockParameters
    {
        $collection = $this->specificationLoader->loadMockParameters($this->specificationUrl);

        $parameters = null;

        /** @var MockParameters $collectionParameters */
        foreach ($collection as $collectionParameters) {
            if ($httpMethod === $collectionParameters->httpMethod && $uri === $collectionParameters->path) {
                $parameters = $collectionParameters;

                break;
            }
        }

        return $parameters;
    }
}
