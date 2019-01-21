<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utility;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class UriLoader
{
    private const LAST_MODIFIED = 'Last-Modified';

    /** @var ClientInterface */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function loadFileContents(string $uri): string
    {
        if ($this->isUrl($uri)) {
            $response = $this->client->request('GET', $uri);
            $stream = $response->getBody();
            $contents = $stream->getContents();
        } else {
            $contents = file_get_contents($uri);
        }

        return $contents;
    }

    public function getTimestamp(string $uri): \DateTimeInterface
    {
        if ($this->isUrl($uri)) {
            $response = $this->client->request('HEAD', $uri);
            $timestamp = $this->getLastModified($response);
        } else {
            $unixTimestamp = filemtime($uri);
            $timestamp = new \DateTime();
            $timestamp->setTimestamp($unixTimestamp);
        }

        return \DateTimeImmutable::createFromMutable($timestamp);
    }

    private function isUrl(string $uri): bool
    {
        $scheme = parse_url($uri, PHP_URL_SCHEME);

        return $scheme === 'http' || $scheme === 'https';
    }

    private function getLastModified(ResponseInterface $response): \DateTime
    {
        if ($response->hasHeader(self::LAST_MODIFIED)) {
            $lastModified = $response->getHeaderLine(self::LAST_MODIFIED);
            $timestamp = new \DateTime($lastModified);
        } else {
            $timestamp = new \DateTime();
        }

        return $timestamp;
    }
}
