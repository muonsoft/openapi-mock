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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class RequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $event->setResponse(new Response($request->getPathInfo()));
    }
}
