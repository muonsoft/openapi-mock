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
use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ResponseStatusNegotiator
{
    public function negotiateResponseStatus(Request $request, MockParameters $parameters): int
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

        if (\count($successCodes) !== 0) {
            $bestStatusCode = $successCodes[0];
        } elseif (\count($errorsCodes) !== 0) {
            $bestStatusCode = $errorsCodes[0];
        } else {
            throw new MockGenerationException('Mock response not found.');
        }

        return $bestStatusCode;
    }
}
