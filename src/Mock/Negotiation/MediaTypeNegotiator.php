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

use App\Mock\Parameters\MockResponse;
use Negotiation\Accept;
use Negotiation\Negotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MediaTypeNegotiator
{
    /** @var Negotiator */
    private $negotiator;

    public function __construct(Negotiator $negotiator)
    {
        $this->negotiator = $negotiator;
    }

    public function negotiateMediaType(Request $request, MockResponse $response): string
    {
        $contentTypes = $this->collectContentTypesFromResponse($response);
        $acceptHeader = $request->headers->get('Accept', '');

        if ($acceptHeader === '') {
            $bestMediaType = $contentTypes[0];
        } else {
            $bestMediaType = $this->getBestMediaType($acceptHeader, $contentTypes);
        }

        return $bestMediaType;
    }

    private function collectContentTypesFromResponse(MockResponse $response): array
    {
        $contentTypes = [];

        foreach ($response->content->getKeys() as $contentType) {
            $contentTypes[] = $contentType;
        }

        return $contentTypes;
    }

    private function getBestMediaType(string $acceptHeader, array $contentTypes): string
    {
        /** @var Accept $mediaType */
        $mediaType = $this->negotiator->getBest($acceptHeader, $contentTypes);

        if ($mediaType === null) {
            throw new UnsupportedMediaTypeHttpException();
        }

        return $mediaType->getValue();
    }
}
