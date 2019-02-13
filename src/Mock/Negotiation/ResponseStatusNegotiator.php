<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Negotiation;

use App\Mock\Exception\MockGenerationException;
use App\Mock\Parameters\Endpoint;
use App\Mock\Parameters\MockResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseStatusNegotiator
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function negotiateResponseStatus(Request $request, Endpoint $parameters): int
    {
        $successCodes = [];
        $errorsCodes = [];

        /** @var MockResponse $response */
        foreach ($parameters->responses as $response) {
            if ($response->statusCode >= 200 && $response->statusCode < 300) {
                $successCodes[] = $response->statusCode;
            } else {
                $errorsCodes[] = $response->statusCode;
            }
        }

        if (0 !== \count($successCodes)) {
            $bestStatusCode = $successCodes[0];
        } elseif (0 !== \count($errorsCodes)) {
            $bestStatusCode = $errorsCodes[0];
        } else {
            throw new MockGenerationException('Mock response not found.');
        }

        $this->logger->info(
            sprintf('Best status code "%s" was negotiated for request.', $bestStatusCode),
            ['request' => $request->getUri()]
        );

        return $bestStatusCode;
    }
}
