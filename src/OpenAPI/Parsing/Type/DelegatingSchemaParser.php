<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI\Parsing\Type;

use App\OpenAPI\Parsing\ParserInterface;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class DelegatingSchemaParser implements ParserInterface
{
    private const DEFAULT_TYPE = 'object';
    private const COMBINED_TYPES = [
        'oneOf',
        'anyOf',
        'allOf',
    ];

    /** @var TypeParserLocator */
    private $typeParserLocator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(TypeParserLocator $typeParserLocator, LoggerInterface $logger)
    {
        $this->typeParserLocator = $typeParserLocator;
        $this->logger = $logger;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $schema = $specification->getSchema($pointer);
        $type = $this->detectSchemaType($schema) ?? self::DEFAULT_TYPE;
        $typeParser = $this->typeParserLocator->getTypeParser($type);
        $object = $typeParser->parsePointedSchema($specification, $pointer);

        $this->logger->debug(
            sprintf('Object "%s" was parsed by "%s"', \get_class($object), \get_class($typeParser)),
            [
                'path'   => $pointer->getPath(),
                'object' => $object,
            ]
        );

        return $object;
    }

    private function detectSchemaType(array $schema): ?string
    {
        $type = $this->detectCombinedType($schema);

        if (null === $type && array_key_exists('type', $schema)) {
            $type = $schema['type'];
        }

        return $type;
    }

    private function detectCombinedType(array $schema): ?string
    {
        $type = null;

        foreach (self::COMBINED_TYPES as $combinedType) {
            if (array_key_exists($combinedType, $schema)) {
                $type = $combinedType;

                break;
            }
        }

        return $type;
    }
}
