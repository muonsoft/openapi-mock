<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock;

use App\API\Responder;
use App\Mock\Generation\DataGenerator;
use App\Mock\MockResponseGenerator;
use App\Mock\Negotiation\MediaTypeNegotiator;
use App\Mock\Negotiation\ResponseStatusNegotiator;
use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\MockResponseCollection;
use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\SchemaCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MockResponseGeneratorTest extends TestCase
{
    private const MEDIA_TYPE = 'media_type';
    private const STATUS_CODE = 200;
    private const RESPONSE_DATA = 'response_data';

    /** @var MediaTypeNegotiator */
    private $mediaTypeNegotiator;

    /** @var ResponseStatusNegotiator */
    private $responseStatusNegotiator;

    /** @var DataGenerator */
    private $dataGenerator;

    /** @var Responder */
    private $responder;

    protected function setUp(): void
    {
        $this->mediaTypeNegotiator = \Phake::mock(MediaTypeNegotiator::class);
        $this->responseStatusNegotiator = \Phake::mock(ResponseStatusNegotiator::class);
        $this->dataGenerator = \Phake::mock(DataGenerator::class);
        $this->responder = \Phake::mock(Responder::class);
    }

    /** @test */
    public function generateResponse_requestCorrespondsMockParameters_mockResponseCreated(): void
    {
        $generator = $this->createMockResponseGenerator();
        $request = new Request();
        $schema = new Schema();
        $parameters = $this->givenMockParametersWithStatusCodeAndMediaTypeWithSchema($schema);
        $this->givenMediaTypeNegotiator_negotiateMediaType_returnsMediaType(self::MEDIA_TYPE);
        $this->givenResponseStatusNegotiator_negotiateResponseStatus_returnsStatusCode(self::STATUS_CODE);
        $this->givenDataGenerator_generateData_returnsResponseData(self::RESPONSE_DATA);
        $responderResponse = $this->givenResponder_createResponse_returnsResponse();

        $response = $generator->generateResponse($request, $parameters);

        $this->assertNotNull($response);
        $this->assertMediaTypeGenerator_negotiateMediaType_wasCalledOnceWithRequestAndMockResponse(
            $request,
            $parameters->responses->get(self::STATUS_CODE)
        );
        $this->assertResponseStatusNegotiator_negotiateResponseStatus_wasCalledOnceWithRequestAndParameters($request, $parameters);
        $this->assertDataGenerator_generateData_wasCalledOnceWithSchema($schema);
        $this->assertResponder_createResponse_wasCalledOnceWithStatusCodeAndMediaTypeAndData(
            self::STATUS_CODE,
            self::MEDIA_TYPE,
            self::RESPONSE_DATA
        );
        $this->assertSame($responderResponse, $response);
    }

    private function createMockResponseGenerator(): MockResponseGenerator
    {
        return new MockResponseGenerator(
            $this->mediaTypeNegotiator,
            $this->responseStatusNegotiator,
            $this->dataGenerator,
            $this->responder
        );
    }

    private function assertMediaTypeGenerator_negotiateMediaType_wasCalledOnceWithRequestAndMockResponse(
        Request $request,
        MockResponse $response
    ): void {
        \Phake::verify($this->mediaTypeNegotiator)
            ->negotiateMediaType($request, $response);
    }

    private function assertResponseStatusNegotiator_negotiateResponseStatus_wasCalledOnceWithRequestAndParameters(
        Request $request,
        MockParameters $parameters
    ): void {
        \Phake::verify($this->responseStatusNegotiator)
            ->negotiateResponseStatus($request, $parameters);
    }

    private function givenMediaTypeNegotiator_negotiateMediaType_returnsMediaType(string $mediaType): void
    {
        \Phake::when($this->mediaTypeNegotiator)
            ->negotiateMediaType(\Phake::anyParameters())
            ->thenReturn($mediaType);
    }

    private function givenResponseStatusNegotiator_negotiateResponseStatus_returnsStatusCode(int $statusCode): void
    {
        \Phake::when($this->responseStatusNegotiator)
            ->negotiateResponseStatus(\Phake::anyParameters())
            ->thenReturn($statusCode);
    }

    private function assertDataGenerator_generateData_wasCalledOnceWithSchema(Schema $schema): void
    {
        \Phake::verify($this->dataGenerator)
            ->generateData($schema);
    }

    private function givenDataGenerator_generateData_returnsResponseData(string $responseData): void
    {
        \Phake::when($this->dataGenerator)
            ->generateData(\Phake::anyParameters())
            ->thenReturn($responseData);
    }

    private function assertResponder_createResponse_wasCalledOnceWithStatusCodeAndMediaTypeAndData(
        int $statusCode,
        string $mediaType,
        string $responseData
    ): void {
        \Phake::verify($this->responder)
            ->createResponse($statusCode, $mediaType, $responseData);
    }

    private function givenResponder_createResponse_returnsResponse(): Response
    {
        $responderResponse = new Response();

        \Phake::when($this->responder)
            ->createResponse(\Phake::anyParameters())
            ->thenReturn($responderResponse);

        return $responderResponse;
    }

    private function givenMockParametersWithStatusCodeAndMediaTypeWithSchema(Schema $schema): MockParameters
    {
        $parameters = new MockParameters();
        $mockResponse = new MockResponse();

        $mockResponse->content = new SchemaCollection([
            self::MEDIA_TYPE => $schema
        ]);
        $parameters->responses = new MockResponseCollection([
            self::STATUS_CODE => $mockResponse
        ]);

        return $parameters;
    }
}
