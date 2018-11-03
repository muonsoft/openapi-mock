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

use App\Mock\Negotiation\ResponseStatusNegotiator;
use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseStatusNegotiatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider statusCodesProvider
     */
    public function negotiateResponseStatus_givenResponsesInMockParameters_bestResponseStatusCodeReturned(
        array $responseStatusCodes,
        int $bestResponseStatusCode
    ): void {
        $negotiator = new ResponseStatusNegotiator();
        $parameters = $this->givenMockParametersWithResponsesWithStatusCodes($responseStatusCodes);

        $statusCode = $negotiator->negotiateResponseStatus(new Request(), $parameters);

        $this->assertSame($bestResponseStatusCode, $statusCode);
    }

    /**
     * @test
     * @expectedException \App\Mock\Exception\MockGenerationException
     * @expectedExceptionMessage Mock response not found
     */
    public function negotiateResponseStatus_noResponsesInMockParameters_exceptionThrown(): void
    {
        $negotiator = new ResponseStatusNegotiator();
        $parameters = new MockParameters();

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

    private function givenMockParametersWithResponsesWithStatusCodes(array $responseStatusCodes): MockParameters
    {
        $parameters = new MockParameters();

        foreach ($responseStatusCodes as $responseStatusCode) {
            $response = new MockResponse();
            $response->statusCode = $responseStatusCode;
            $parameters->responses->set($responseStatusCode, $response);
        }

        return $parameters;
    }
}
