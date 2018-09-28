<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock;

use App\API\Responder;
use App\Mock\Generation\DataGenerator;
use App\Mock\Negotiation\MediaTypeNegotiator;
use App\Mock\Negotiation\ResponseStatusNegotiator;
use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockResponse;
use App\Mock\Parameters\Schema\Schema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockResponseGenerator
{
    private const UNSUPPORTED_MEDIA_TYPE_STATUS_CODE = 406;
    private const DEFAULT_MEDIA_TYPE = 'text/html';
    private const UNSUPPORTED_MEDIA_TYPE = 'Unsupported media type';

    /** @var MediaTypeNegotiator */
    private $mediaTypeNegotiator;

    /** @var ResponseStatusNegotiator */
    private $responseStatusNegotiator;

    /** @var DataGenerator */
    private $dataGenerator;

    /** @var Responder */
    private $responder;

    /** @var int */
    private $statusCode;

    /** @var string */
    private $mediaType;

    public function __construct(
        MediaTypeNegotiator $mediaTypeNegotiator,
        ResponseStatusNegotiator $responseStatusNegotiator,
        DataGenerator $dataGenerator,
        Responder $responder
    ) {
        $this->mediaTypeNegotiator = $mediaTypeNegotiator;
        $this->responseStatusNegotiator = $responseStatusNegotiator;
        $this->dataGenerator = $dataGenerator;
        $this->responder = $responder;
    }

    public function generateResponse(Request $request, MockParameters $parameters): Response
    {
        $this->negotiateResponseStatusCode($request, $parameters);
        $this->negotiateMediaType($request, $parameters);
        $schema = $this->detectResponseDataSchema($parameters);

        if (null === $schema) {
            $response = $this->createUnsupportedMediaTypeResponse();
        } else {
            $response = $this->generateMockResponseBySchema($schema);
        }

        return $response;
    }

    private function negotiateResponseStatusCode(Request $request, MockParameters $parameters): void
    {
        $this->statusCode = $this->responseStatusNegotiator->negotiateResponseStatus($request, $parameters);
    }

    private function negotiateMediaType(Request $request, MockParameters $parameters): void
    {
        $this->mediaType = $this->mediaTypeNegotiator->negotiateMediaType($request, $parameters);
    }

    private function detectResponseDataSchema(MockParameters $parameters): ?Schema
    {
        /** @var MockResponse $mockResponse */
        $mockResponse = $parameters->responses->get($this->statusCode);

        if (null === $mockResponse) {
            throw new \DomainException('Invalid response status code negotiated');
        }

        return $mockResponse->content->get($this->mediaType);
    }

    private function createUnsupportedMediaTypeResponse(): Response
    {
        return $this->responder->createResponse(
            self::UNSUPPORTED_MEDIA_TYPE_STATUS_CODE,
            self::DEFAULT_MEDIA_TYPE,
            self::UNSUPPORTED_MEDIA_TYPE
        );
    }

    private function generateMockResponseBySchema(Schema $schema): Response
    {
        $responseData = $this->dataGenerator->generateData($schema);

        return $this->responder->createResponse($this->statusCode, $this->mediaType, $responseData);
    }
}
