<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value;

use App\Mock\Parameters\Schema\Type\InvalidType;
use App\Mock\Parameters\Schema\Type\TypeInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidValueGenerator implements ValueGeneratorInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function generateValue(TypeInterface $type): void
    {
        \assert($type instanceof InvalidType);

        $this->logger->warning(
            sprintf(
                'Message generation was ignored due to invalid type with error: "%s".',
                $type->getError()
            )
        );
    }
}
