<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utility;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EncoderDecorator implements EncoderInterface
{
    /** @var EncoderInterface */
    private $decorated;

    public function __construct(EncoderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function encode($data, $format, array $context = [])
    {
        if ('json' !== $format) {
            $data = json_decode(json_encode($data), true);
        }

        return $this->decorated->encode($data, $format, $context);
    }

    public function supportsEncoding($format): bool
    {
        return $this->decorated->supportsEncoding($format);
    }
}
