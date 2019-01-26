#!/usr/bin/env php

<?php

use App\Kernel;
use Spiral\Goridge\SocketRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
    throw new \RuntimeException('APP_ENV environment variable is not defined.');
}

$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ('prod' !== $env));

if ($debug) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$kernel = new Kernel($env, $debug);
$relay = new SocketRelay('/var/run/road-runner.sock', null, SocketRelay::SOCK_UNIX);
$worker = new Worker($relay);
$psr7 = new PSR7Client($worker);
$httpFoundationFactory = new HttpFoundationFactory();
$diactorosFactory = new DiactorosFactory();

while ($req = $psr7->acceptRequest()) {
    try {
        $request = $httpFoundationFactory->createRequest($req);
        $response = $kernel->handle($request);
        $psr7->respond($diactorosFactory->createResponse($response));
        $kernel->terminate($request, $response);
        $kernel->reboot(null);
    } catch (\Throwable $e) {
        $psr7->getWorker()->error((string)$e);
    }
}
