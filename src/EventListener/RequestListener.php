<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use App\API\RequestHandler;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestListener
{
    /** @var RequestHandler */
    private $requestHandler;

    public function __construct(RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $this->requestHandler->handleRequest($request);
        $event->setResponse($response);
    }
}
