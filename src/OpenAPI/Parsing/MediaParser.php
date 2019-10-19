<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing;

use App\Mock\Parameters\Schema\Schema;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MediaParser
{
    /** @var ParserInterface */
    private $schemaParser;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ParserInterface $schemaParser, LoggerInterface $logger)
    {
        $this->schemaParser = $schemaParser;
        $this->logger = $logger;
    }

    public function parseMediaScheme(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        string $mediaType
    ): SpecificationObjectMarkerInterface {
        /** @var Schema $schema */
        $schema = $this->schemaParser->parsePointedSchema($specification, $pointer);

        if ('text/html' === $mediaType) {
            if (!$schema->value instanceof StringType) {
                $schema->value = new StringType();
                $this->logger->warning('Only string types are supported for media type "text/html".');
            }

            $schema->value->format = 'html';
        }

        return $schema;
    }
}
