<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\API;

use App\Mock\EndpointRepository;
use App\Mock\MockResponseGenerator;
use App\Mock\Parameters\Endpoint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestHandler
{
    /** @var EndpointRepository */
    private $repository;

    /** @var MockResponseGenerator */
    private $responseGenerator;

    public function __construct(EndpointRepository $repository, MockResponseGenerator $responseGenerator)
    {
        $this->repository = $repository;
        $this->responseGenerator = $responseGenerator;
    }

    public function handleRequest(Request $request): Response
    {
        $mockEndpoint = $this->findMockEndpointForRequest($request);

        if (null === $mockEndpoint) {
            $response = new Response('API endpoint not found.', Response::HTTP_NOT_FOUND);
        } else {
            $response = $this->responseGenerator->generateResponse($request, $mockEndpoint);
        }

        return $response;
    }

    private function findMockEndpointForRequest(Request $request): ?Endpoint
    {
        $httpMethod = $request->getMethod();
        $requestUri = $request->getPathInfo();

        return $this->repository->findMockEndpoint($httpMethod, $requestUri);
    }
}
