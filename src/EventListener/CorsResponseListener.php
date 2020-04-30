<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CorsResponseListener
{
    /** @var bool */
    private $cors_enable;

    public function __construct(bool $cors_enable)
    {
        $this->cors_enable = $cors_enable;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$this->cors_enable) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->isMethod('OPTIONS')) {
            return;
        }

        $response = $event->getResponse();

        if ($response->getStatusCode() !== Response::HTTP_NOT_FOUND) {
            return;
        }

        $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin', '*'));

        $response->headers->set(
            'Access-Control-Allow-Methods',
            $request->headers->get('Access-Control-Request-Method', 'GET,POST,PUT,DELETE')
        );

        if ($requestHeaders = $request->headers->get('Access-Control-Request-Headers')) {
            $response->headers->set('Access-Control-Allow-Headers', $requestHeaders);
        }

        $response->setContent('');
        $response->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
