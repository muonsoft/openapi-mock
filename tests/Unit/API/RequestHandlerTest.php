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
use App\Mock\EndpointRepository;
use App\Mock\MockResponseGenerator;
use App\Mock\Parameters\Endpoint;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestHandlerTest extends TestCase
{
    private const REQUEST_URI = '/request_uri';

    /** @var EndpointRepository */
    private $repository;

    /** @var MockResponseGenerator */
    private $responseGenerator;

    protected function setUp(): void
    {
        $this->repository = \Phake::mock(EndpointRepository::class);
        $this->responseGenerator = \Phake::mock(MockResponseGenerator::class);
    }

    /** @test */
    public function handleRequest_requestWithExpectedMethodAndUri_mockEndpointFoundAndResponseGeneratedAndReturned(): void
    {
        $handler = $this->createRequestHandler();
        $request = $this->givenRequest(Request::METHOD_GET, self::REQUEST_URI);
        $mockEndpoint = $this->givenMockEndpointRepository_findMockEndpoint_returnsMockEndpoint();
        $expectedResponse = $this->givenMockResponseGenerator_generateResponse_returnsResponse();

        $response = $handler->handleRequest($request);

        $this->assertMockEndpointRepository_findMockEndpoint_wasCalledOnceWithHttpMethodAndRequestUri(Request::METHOD_GET, self::REQUEST_URI);
        $this->assertMockResponseGenerator_generateResponse_wasCalledOnceWithRequestAndMockEndpoint($request, $mockEndpoint);
        $this->assertSame($expectedResponse, $response);
    }

    /** @test */
    public function handleRequest_requestWithNotExpectedMethodOrUri_mockEndpointNotFoundAndNotFoundResponseReturned(): void
    {
        $handler = $this->createRequestHandler();
        $request = $this->givenRequest(Request::METHOD_GET, self::REQUEST_URI);
        $this->givenMockEndpointRepository_findMockEndpoint_returnsNull();

        $response = $handler->handleRequest($request);

        $this->assertMockEndpointRepository_findMockEndpoint_wasCalledOnceWithHttpMethodAndRequestUri(Request::METHOD_GET, self::REQUEST_URI);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('API endpoint not found.', $response->getContent());
    }

    private function assertMockEndpointRepository_findMockEndpoint_wasCalledOnceWithHttpMethodAndRequestUri(
        string $httpMethod,
        string $requestUri
    ): void {
        \Phake::verify($this->repository)
            ->findMockEndpoint($httpMethod, $requestUri);
    }

    private function assertMockResponseGenerator_generateResponse_wasCalledOnceWithRequestAndMockEndpoint(
        Request $request,
        Endpoint $mockEndpoint
    ): void {
        \Phake::verify($this->responseGenerator)
            ->generateResponse($request, $mockEndpoint);
    }

    private function givenRequest($httpMethod, $requestUri): Request
    {
        $request = new Request([], [], [], [], [], ['REQUEST_URI' => $requestUri]);
        $request->setMethod($httpMethod);

        return $request;
    }

    private function givenMockEndpointRepository_findMockEndpoint_returnsMockEndpoint(): Endpoint
    {
        $mockEndpoint = new Endpoint();

        \Phake::when($this->repository)
            ->findMockEndpoint(\Phake::anyParameters())
            ->thenReturn($mockEndpoint);

        return $mockEndpoint;
    }

    private function givenMockEndpointRepository_findMockEndpoint_returnsNull(): void
    {
        \Phake::when($this->repository)
            ->findMockEndpoint(\Phake::anyParameters())
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
        return new RequestHandler($this->repository, $this->responseGenerator);
    }
}
