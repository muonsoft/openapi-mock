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

use App\API\Responder;
use App\Mock\Exception\NotSupportedException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class ResponderTest extends TestCase
{
    private const DATA = 'data';
    private const ENCODED_DATA = 'encoded_data';

    /** @var EncoderInterface */
    private $encoder;

    protected function setUp(): void
    {
        $this->encoder = \Phake::mock(EncoderInterface::class);
    }

    /**
     * @test
     * @dataProvider statusCodeAndMediaTypeAndEncodingFormatProvider
     */
    public function createResponse_statusCodeAndMediaTypeAndData_formatDetectedByMediaTypeAndDataEncodedAndReturned(
        int $statusCode,
        string $mediaType,
        string $expectedEncodingFormat
    ): void {
        $responder = $this->creteResponder();
        $contentType = sprintf('%s; charset=utf-8', $mediaType);
        $this->givenEncoder_encode_returnsData(self::ENCODED_DATA);

        $response = $responder->createResponse($statusCode, $mediaType, self::DATA);

        $this->assertEncoder_encode_wasCalledOnceWithDataAndFormat(self::DATA, $expectedEncodingFormat);
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame($contentType, $response->headers->get('Content-Type'));
        $this->assertSame(self::ENCODED_DATA, $response->getContent());
    }

    /** @test */
    public function createResponse_statusCodeAndNotSupportedMediaTypeAndData_exceptionThrown(): void
    {
        $responder = $this->creteResponder();

        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Not supported media type');

        $responder->createResponse(Response::HTTP_OK, 'text/html', self::DATA);
    }

    public function statusCodeAndMediaTypeAndEncodingFormatProvider(): array
    {
        return [
            [
                Response::HTTP_OK,
                'application/json',
                'json',
            ],
            [
                Response::HTTP_CREATED,
                'application/json',
                'json',
            ],
            [
                Response::HTTP_OK,
                'application/ld+json',
                'json',
            ],
            [
                Response::HTTP_OK,
                'application/xml',
                'xml',
            ],
        ];
    }

    private function assertEncoder_encode_wasCalledOnceWithDataAndFormat(string $data, string $expectedEncodingFormat): void
    {
        \Phake::verify($this->encoder)
            ->encode($data, $expectedEncodingFormat);
    }

    private function givenEncoder_encode_returnsData(string $data): void
    {
        \Phake::when($this->encoder)
            ->encode(\Phake::anyParameters())
            ->thenReturn($data);
    }

    private function creteResponder(): Responder
    {
        return new Responder($this->encoder, new NullLogger());
    }
}
