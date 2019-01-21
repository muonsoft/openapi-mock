<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Utility;

use App\Utility\UriLoader;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class UriLoaderTest extends TestCase
{
    private const DUMMY_JSON_FILE = __DIR__ . '/../../Resources/dummy.json';
    private const REMOTE_FILE_NAME = 'http://example.com/remote.log';
    private const REMOTE_FILE_UNIX_TIMESTAMP = 1376092475;
    private const REMOTE_FILE_LAST_MODIFIED = 'Fri, 09 Aug 2013 23:54:35 GMT';

    /** @var ClientInterface */
    private $client;

    protected function setUp(): void
    {
        $this->client = \Phake::mock(ClientInterface::class);
    }

    /** @test */
    public function loadFileContents_localFileName_fileContentsLoadedAndReturned(): void
    {
        $loader = $this->createUriLoader();

        $contents = $loader->loadFileContents(self::DUMMY_JSON_FILE);

        \Phake::verifyNoInteraction($this->client);
        $this->assertStringEqualsFile(self::DUMMY_JSON_FILE, $contents);
    }

    /** @test */
    public function loadFileContents_remoteFileName_fileContentsLoadedAndReturned(): void
    {
        $loader = $this->createUriLoader();
        $response = $this->givenClient_request_returnsResponse();
        $stream = $this->givenResponse_getBody_returnsStream($response);
        $expectedContents = $this->givenStream_getContents_returnsContents($stream);

        $contents = $loader->loadFileContents(self::REMOTE_FILE_NAME);

        $this->assertSame($expectedContents, $contents);
        $this->assertClient_request_wasCalledOnceWithMethodAndUri('GET', self::REMOTE_FILE_NAME);
        $this->assertResponse_getBody_wasCalledOnce($response);
        $this->assertStream_getContents_wasCalledOnce($stream);
    }

    /** @test */
    public function getTimestamp_localFileName_timestampReturned(): void
    {
        $loader = $this->createUriLoader();

        $timestamp = $loader->getTimestamp(self::DUMMY_JSON_FILE);

        \Phake::verifyNoInteraction($this->client);
        $this->assertGreaterThan(0, $timestamp->getTimestamp());
    }

    /** @test */
    public function getTimestamp_remoteFileNameWithLastModifiedHeader_timestampReturned(): void
    {
        $loader = $this->createUriLoader();
        $response = $this->givenClient_request_returnsResponse();
        $this->givenResponse_hasHeader_returnsBool($response, true);
        $this->givenResponse_getHeaderLine_returnsDateTime($response, self::REMOTE_FILE_LAST_MODIFIED);

        $timestamp = $loader->getTimestamp(self::REMOTE_FILE_NAME);

        $this->assertSame(self::REMOTE_FILE_UNIX_TIMESTAMP, $timestamp->getTimestamp());
        $this->assertClient_request_wasCalledOnceWithMethodAndUri('HEAD', self::REMOTE_FILE_NAME);
        $this->assertResponse_hasHeader_wasCalledOnceWithHeader($response, 'Last-Modified');
        $this->assertResponse_getHeaderLine_wasCalledOnceWithHeader($response, 'Last-Modified');
    }

    /** @test */
    public function getTimestamp_remoteFileNameWithoutLastModifiedHeader_nowTimestampReturned(): void
    {
        $loader = $this->createUriLoader();
        $response = $this->givenClient_request_returnsResponse();
        $this->givenResponse_hasHeader_returnsBool($response, false);

        $timestamp = $loader->getTimestamp(self::REMOTE_FILE_NAME);

        $this->assertNotSame(self::REMOTE_FILE_UNIX_TIMESTAMP, $timestamp->getTimestamp());
        $this->assertClient_request_wasCalledOnceWithMethodAndUri('HEAD', self::REMOTE_FILE_NAME);
        $this->assertResponse_hasHeader_wasCalledOnceWithHeader($response, 'Last-Modified');
        $this->assertResponse_getHeaderLine_wasNeverCalled($response);
    }

    private function createUriLoader(): UriLoader
    {
        return new UriLoader($this->client);
    }

    private function assertClient_request_wasCalledOnceWithMethodAndUri(string $method, string $uri): void
    {
        \Phake::verify($this->client)
            ->request($method, $uri);
    }

    private function assertResponse_getBody_wasCalledOnce(ResponseInterface $response): void
    {
        \Phake::verify($response)
            ->getBody();
    }

    private function givenClient_request_returnsResponse(): ResponseInterface
    {
        $response = \Phake::mock(ResponseInterface::class);

        \Phake::when($this->client)
            ->request(\Phake::anyParameters())
            ->thenReturn($response);

        return $response;
    }

    private function assertStream_getContents_wasCalledOnce(StreamInterface $stream): void
    {
        \Phake::verify($stream)
            ->getContents();
    }

    private function givenResponse_getBody_returnsStream(ResponseInterface $response): StreamInterface
    {
        $stream = \Phake::mock(StreamInterface::class);

        \Phake::when($response)
            ->getBody()
            ->thenReturn($stream);

        return $stream;
    }

    private function givenStream_getContents_returnsContents(StreamInterface $stream): string
    {
        $expectedContents = 'contents';

        \Phake::when($stream)
            ->getContents()
            ->thenReturn($expectedContents);

        return $expectedContents;
    }

    private function assertResponse_hasHeader_wasCalledOnceWithHeader(ResponseInterface $response, string $header): void
    {
        \Phake::verify($response)
            ->hasHeader($header);
    }

    private function assertResponse_getHeaderLine_wasCalledOnceWithHeader(ResponseInterface $response, string $header): void
    {
        \Phake::verify($response)
            ->getHeaderLine($header);
    }

    private function givenResponse_hasHeader_returnsBool(ResponseInterface $response, bool $hasHeader): void
    {
        \Phake::when($response)
            ->hasHeader(\Phake::anyParameters())
            ->thenReturn($hasHeader);
    }

    private function givenResponse_getHeaderLine_returnsDateTime(ResponseInterface $response, string $lastModifiedDatetime): void
    {
        \Phake::when($response)
            ->getHeaderLine(\Phake::anyParameters())
            ->thenReturn($lastModifiedDatetime);
    }

    private function assertResponse_getHeaderLine_wasNeverCalled(ResponseInterface $response): void
    {
        \Phake::verify($response, \Phake::never())
            ->getHeaderLine(\Phake::anyParameters());
    }
}
