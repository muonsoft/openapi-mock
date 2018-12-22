<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\API;

use App\API\RequestHandler;
use App\Mock\MockParametersRepository;
use App\Mock\MockResponseGenerator;
use App\Mock\Parameters\MockParameters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandlerTest extends TestCase
{
    private const REQUEST_URI = '/request_uri';

    /** @var MockParametersRepository */
    private $repository;

    /** @var MockResponseGenerator */
    private $responseGenerator;

    protected function setUp(): void
    {
        $this->repository = \Phake::mock(MockParametersRepository::class);
        $this->responseGenerator = \Phake::mock(MockResponseGenerator::class);
    }

    /** @test */
    public function handleRequest_requestWithExpectedMethodAndUri_mockParametersFoundAndResponseGeneratedAndReturned(): void
    {
        $handler = $this->createRequestHandler();
        $request = $this->givenRequest(Request::METHOD_GET, self::REQUEST_URI);
        $mockParameters = $this->givenMockParametersRepository_findMockParameters_returnsMockParameters();
        $expectedResponse = $this->givenMockResponseGenerator_generateResponse_returnsResponse();

        $response = $handler->handleRequest($request);

        $this->assertMockParametersRepository_findMockParameters_wasCalledOnceWithHttpMethodAndRequestUri(Request::METHOD_GET, self::REQUEST_URI);
        $this->assertMockResponseGenerator_generateResponse_wasCalledOnceWithRequestAndMockParameters($request, $mockParameters);
        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function handleRequest_requestWithNotExpectedMethodOrUri_mockParametersNotFoundAndNotFoundResponseReturned(): void
    {
        $handler = $this->createRequestHandler();
        $request = $this->givenRequest(Request::METHOD_GET, self::REQUEST_URI);
        $this->givenMockParametersRepository_findMockParameters_returnsNull();

        $response = $handler->handleRequest($request);

        $this->assertMockParametersRepository_findMockParameters_wasCalledOnceWithHttpMethodAndRequestUri(Request::METHOD_GET, self::REQUEST_URI);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('API endpoint not found.', $response->getContent());
    }

    private function assertMockParametersRepository_findMockParameters_wasCalledOnceWithHttpMethodAndRequestUri(
        string $httpMethod,
        string $requestUri
    ): void {
        \Phake::verify($this->repository)
            ->findMockParameters($httpMethod, $requestUri);
    }

    private function assertMockResponseGenerator_generateResponse_wasCalledOnceWithRequestAndMockParameters(
        Request $request,
        MockParameters $mockParameters
    ): void {
        \Phake::verify($this->responseGenerator)
            ->generateResponse($request, $mockParameters);
    }

    private function givenRequest($httpMethod, $requestUri): Request
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => $requestUri]);
        $request->setMethod($httpMethod);

        return $request;
    }

    private function givenMockParametersRepository_findMockParameters_returnsMockParameters(): MockParameters
    {
        $mockParameters = new MockParameters();

        \Phake::when($this->repository)
            ->findMockParameters(\Phake::anyParameters())
            ->thenReturn($mockParameters);

        return $mockParameters;
    }

    private function givenMockParametersRepository_findMockParameters_returnsNull(): void
    {
        \Phake::when($this->repository)
            ->findMockParameters(\Phake::anyParameters())
            ->thenReturn(null);
    }

    private function givenMockResponseGenerator_generateResponse_returnsResponse(): Response
    {
        $response = new Response();

        \Phake::when($this->responseGenerator)
            ->generateResponse(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }

    private function createRequestHandler(): RequestHandler
    {
        $handler = new RequestHandler($this->repository, $this->responseGenerator);
        return $handler;
    }
}
