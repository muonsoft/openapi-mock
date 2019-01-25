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

use App\Mock\MockParametersRepository;
use App\Mock\MockResponseGenerator;
use App\Mock\Parameters\MockParameters;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestHandler
{
    /** @var MockParametersRepository */
    private $repository;

    /** @var MockResponseGenerator */
    private $responseGenerator;

    public function __construct(MockParametersRepository $repository, MockResponseGenerator $responseGenerator)
    {
        $this->repository = $repository;
        $this->responseGenerator = $responseGenerator;
    }

    public function handleRequest(Request $request): Response
    {
        $mockParameters = $this->findMockParametersForRequest($request);

        if (null === $mockParameters) {
            $response = new Response('API endpoint not found.', Response::HTTP_NOT_FOUND);
        } else {
            $response = $this->responseGenerator->generateResponse($request, $mockParameters);
        }

        return $response;
    }

    private function findMockParametersForRequest(Request $request): ?MockParameters
    {
        $httpMethod = $request->getMethod();
        $requestUri = $request->getPathInfo();

        return $this->repository->findMockParameters($httpMethod, $requestUri);
    }
}
