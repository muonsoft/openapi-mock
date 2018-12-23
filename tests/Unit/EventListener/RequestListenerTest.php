<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\EventListener;

use App\API\RequestHandler;
use App\EventListener\RequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListenerTest extends TestCase
{
    /** @var RequestHandler */
    private $requestHandler;

    /** @var GetResponseEvent */
    private $event;

    protected function setUp(): void
    {
        $this->requestHandler = \Phake::mock(RequestHandler::class);
        $this->event = \Phake::mock(GetResponseEvent::class);
    }

    /** @test */
    public function onKernelRequest_eventWithRequest_requestHandledAndResponseSetToEvent(): void
    {
        $listener = new RequestListener($this->requestHandler);
        $request = $this->givenGetResponseEvent_getRequest_returnsRequest();
        $response = $this->givenRequestHandler_handleRequest_returnsResponse();

        $listener->onKernelRequest($this->event);

        $this->assertGetResponseEvent_getRequest_wasCalledOnce();
        $this->assertRequestHandler_handleRequest_wasCalledOnceWithRequest($request);
        $this->assertGetResponseEvent_setResponse_wasCalledOnceWithResponse($response);
    }

    private function assertGetResponseEvent_getRequest_wasCalledOnce(): void
    {
        \Phake::verify($this->event)
            ->getRequest();
    }

    private function assertRequestHandler_handleRequest_wasCalledOnceWithRequest(Request $request): void
    {
        \Phake::verify($this->requestHandler)
            ->handleRequest($request);
    }

    private function assertGetResponseEvent_setResponse_wasCalledOnceWithResponse(Response $response): void
    {
        \Phake::verify($this->event)
            ->setResponse($response);
    }

    private function givenGetResponseEvent_getRequest_returnsRequest(): Request
    {
        $request = new Request();

        \Phake::when($this->event)
            ->getRequest()
            ->thenReturn($request);

        return $request;
    }

    private function givenRequestHandler_handleRequest_returnsResponse(): Response
    {
        $response = new Response();

        \Phake::when($this->requestHandler)
            ->handleRequest(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }
}
