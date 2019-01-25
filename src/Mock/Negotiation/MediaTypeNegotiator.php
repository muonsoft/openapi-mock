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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MediaTypeNegotiator
{
    /** @var Negotiator */
    private $negotiator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(Negotiator $negotiator, LoggerInterface $logger)
    {
        $this->negotiator = $negotiator;
        $this->logger = $logger;
    }

    public function negotiateMediaType(Request $request, MockResponse $response): string
    {
        $contentTypes = $this->collectContentTypesFromResponse($response);
        $acceptHeader = $request->headers->get('Accept', '');

        if ('' === $acceptHeader) {
            $bestMediaType = $contentTypes[0];
        } else {
            $bestMediaType = $this->getBestMediaType($acceptHeader, $contentTypes);
        }

        $this->logger->info(
            sprintf('Best media type "%s" was negotiated for request.', $bestMediaType),
            [
                'request' => $request->getUri(),
                'acceptHeader' => $acceptHeader,
            ]
        );

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

        if (null === $mediaType) {
            throw new UnsupportedMediaTypeHttpException();
        }

        return $mediaType->getValue();
    }
}
