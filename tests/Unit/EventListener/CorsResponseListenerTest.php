<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\CorsResponseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CorsResponseListenerTest extends TestCase
{
    /** @var ResponseEvent */
    private $event;

    protected function setUp(): void
    {
        $this->event = \Phake::mock(ResponseEvent::class);
    }

    /** @test */
    public function onUnhandledOptionsRequest_ifFeatureEnabled_handlesCors(): void
    {
        $listener = new CorsResponseListener(true);
        $request = new Request();
        $request->setMethod('OPTIONS');
        $request->headers->set('Origin', 'http://example.tld');
        $response = new Response();
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        \Phake::when($this->event)
            ->getRequest()
            ->thenReturn($request);
        \Phake::when($this->event)
            ->getResponse()
            ->thenReturn($response);

        $listener->onKernelResponse($this->event);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEquals('http://example.tld', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET,POST,PUT,DELETE', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals('', $response->getContent());
    }

    /** @test */
    public function onCorsRequest_ifFeatureEnabled_addsCorsHeaders(): void
    {
        $listener = new CorsResponseListener(true);
        $request = new Request();
        $request->setMethod('GET');
        $request->headers->set('Origin', 'http://example.tld');
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->setContent('a content');
        \Phake::when($this->event)
            ->getRequest()
            ->thenReturn($request);
        \Phake::when($this->event)
            ->getResponse()
            ->thenReturn($response);

        $listener->onKernelResponse($this->event);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('a content', $response->getContent());
        $this->assertEquals('http://example.tld', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET,POST,PUT,DELETE', $response->headers->get('Access-Control-Allow-Methods'));
    }

    /** @test */
    public function onCorsRequest_ifFeatureDisabled_doesNothing(): void
    {
        $listener = new CorsResponseListener(false);
        $request = new Request();
        $request->headers->set('Origin', 'http://example.tld');
        $response = new Response();
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        \Phake::when($this->event)
            ->getRequest()
            ->thenReturn($request);
        \Phake::when($this->event)
            ->getResponse()
            ->thenReturn($response);

        $listener->onKernelResponse($this->event);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals('', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('', $response->headers->get('Access-Control-Allow-Methods'));
    }
}
