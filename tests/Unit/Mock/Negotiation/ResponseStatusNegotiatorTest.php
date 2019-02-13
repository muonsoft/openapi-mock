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

use App\Mock\Exception\MockGenerationException;
use App\Mock\Negotiation\ResponseStatusNegotiator;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\MockResponse;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseStatusNegotiatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider statusCodesProvider
     */
    public function negotiateResponseStatus_givenResponsesInMockEndpoint_bestResponseStatusCodeReturned(
        array $responseStatusCodes,
        int $bestResponseStatusCode
    ): void {
        $negotiator = $this->createResponseStatusNegotiator();
        $parameters = $this->givenMockEndpointWithResponsesWithStatusCodes($responseStatusCodes);

        $statusCode = $negotiator->negotiateResponseStatus(new Request(), $parameters);

        $this->assertSame($bestResponseStatusCode, $statusCode);
    }

    /** @test */
    public function negotiateResponseStatus_noResponsesInMockEndpoint_exceptionThrown(): void
    {
        $negotiator = $this->createResponseStatusNegotiator();
        $parameters = new Endpoint();

        $this->expectException(MockGenerationException::class);
        $this->expectExceptionMessage('Mock response not found');

        $negotiator->negotiateResponseStatus(new Request(), $parameters);
    }

    public function statusCodesProvider(): array
    {
        return [
            [
                [Response::HTTP_OK],
                Response::HTTP_OK,
            ],
            [
                [Response::HTTP_CREATED],
                Response::HTTP_CREATED,
            ],
            [
                [Response::HTTP_BAD_REQUEST, Response::HTTP_OK],
                Response::HTTP_OK,
            ],
            [
                [Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST,
            ],
        ];
    }

    private function givenMockEndpointWithResponsesWithStatusCodes(array $responseStatusCodes): Endpoint
    {
        $parameters = new Endpoint();

        foreach ($responseStatusCodes as $responseStatusCode) {
            $response = new MockResponse();
            $response->statusCode = $responseStatusCode;
            $parameters->responses->set($responseStatusCode, $response);
        }

        return $parameters;
    }

    private function createResponseStatusNegotiator(): ResponseStatusNegotiator
    {
        return new ResponseStatusNegotiator(new NullLogger());
    }
}
