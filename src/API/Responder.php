<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\API;

use App\Mock\Exception\NotSupportedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class Responder
{
    /** @var EncoderInterface */
    private $encoder;

    public function __construct(EncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function createResponse(int $statusCode, string $mediaType, $data): Response
    {
        $format = $this->guessSerializationFormat($mediaType);
        $encodedData = $this->encoder->encode($data, $format);

        $headers = [
            'Content-Type' => sprintf('%s; charset=utf-8', $mediaType)
        ];

        return new Response($encodedData, $statusCode, $headers);
    }

    private function guessSerializationFormat(string $mediaType): string
    {
        if (preg_match('/^application\/.*json$/', $mediaType)) {
            $format = 'json';
        } elseif (preg_match('/^application\/.*xml$/', $mediaType)) {
            $format = 'xml';
        } else {
            throw new NotSupportedException(sprintf('Not supported media type "%s".', $mediaType));
        }

        return $format;
    }
}
