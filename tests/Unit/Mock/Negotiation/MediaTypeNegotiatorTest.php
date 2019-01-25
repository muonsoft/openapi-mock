<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Negotiation;

use App\Mock\Negotiation\MediaTypeNegotiator;
use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\Schema\Schema;
use Negotiation\Negotiator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class MediaTypeNegotiatorTest extends TestCase
{
    private const APPLICATION_JSON = 'application/json';
    private const APPLICATION_LD_JSON = 'application/ld+json';
    private const APPLICATION_XML = 'application/xml';

    /** @var Negotiator */
    private $negotiator;

    protected function setUp(): void
    {
        $this->negotiator = new Negotiator();
    }

    /**
     * @test
     * @dataProvider contentTypesProvider
     */
    public function negotiateMediaType_requestWithGivenAcceptHeaderAndResponseWithContentTypes_bestContentTypeReturned(
        string $acceptHeader,
        array $responseContentTypes,
        string $bestContentType
    ): void {
        $mediaTypeNegotiator = $this->createMediaTypeNegotiator();
        $request = $this->givenRequestWithAcceptHeader($acceptHeader);
        $response = $this->givenMockResponseWithContentTypes($responseContentTypes);

        $mediaType = $mediaTypeNegotiator->negotiateMediaType($request, $response);

        $this->assertSame($bestContentType, $mediaType);
    }

    /** @test */
    public function negotiateMediaType_requestWithJsonInAcceptHeaderAndResponseWithJson_jsonContentReturned(): void
    {
        $mediaTypeNegotiator = $this->createMediaTypeNegotiator();
        $request = $this->givenRequestWithAcceptHeader(self::APPLICATION_XML);
        $response = $this->givenMockResponseWithContentTypes([self::APPLICATION_JSON]);

        $this->expectException(UnsupportedMediaTypeHttpException::class);

        $mediaTypeNegotiator->negotiateMediaType($request, $response);
    }

    public function contentTypesProvider(): array
    {
        return [
            [
                self::APPLICATION_JSON,
                [self::APPLICATION_JSON],
                self::APPLICATION_JSON,
            ],
            [
                self::APPLICATION_LD_JSON,
                [self::APPLICATION_JSON, self::APPLICATION_LD_JSON],
                self::APPLICATION_LD_JSON,
            ],
            [
                self::APPLICATION_XML,
                [self::APPLICATION_JSON, self::APPLICATION_XML],
                self::APPLICATION_XML,
            ],
            [
                '',
                [self::APPLICATION_JSON, self::APPLICATION_LD_JSON],
                self::APPLICATION_JSON,
            ],
        ];
    }

    private function givenRequestWithAcceptHeader(string $acceptHeader): Request
    {
        $request = new Request();
        $request->headers->set('Accept', $acceptHeader);

        return $request;
    }

    private function givenMockResponseWithContentTypes(array $responseContentTypes): MockResponse
    {
        $response = new MockResponse();

        foreach ($responseContentTypes as $contentType) {
            $response->content->set($contentType, new Schema());
        }

        return $response;
    }

    private function createMediaTypeNegotiator(): MediaTypeNegotiator
    {
        return new MediaTypeNegotiator($this->negotiator, new NullLogger());
    }
}
