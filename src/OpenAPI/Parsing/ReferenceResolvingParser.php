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

use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ReferenceResolvingParser
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function resolveReferenceAndParsePointedSchema(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ContextualParserInterface $parser
    ): SpecificationObjectMarkerInterface {
        $schema = $specification->getSchema($pointer);

        if (array_key_exists('$ref', $schema)) {
            $reference = $schema['$ref'];
            $object = $this->parseReferencedSchema($specification, $pointer, $parser, $reference);
        } else {
            $object = $parser->parsePointedSchema($specification, $pointer);
        }

        return $object;
    }

    private function parseReferencedSchema(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ContextualParserInterface $parser,
        string $reference
    ): SpecificationObjectMarkerInterface {
        $this->logger->debug(sprintf('Reference "%s" detected.', $reference), ['path' => $pointer->getPath()]);

        $this->validateReference($reference, $pointer);

        $object = $specification->findResolvedObject($reference);

        if ($object === null) {
            $referencePointer = $this->createReferencePointer($reference);
            $object = $parser->parsePointedSchema($specification, $referencePointer);
            $specification->setResolvedObject($reference, $object);

            $this->logger->debug(
                sprintf('Object "%s" was resolved and set to specification.', \get_class($object)),
                [
                    'path' => $pointer->getPath(),
                    'referencePath' => $referencePointer->getPath(),
                ]
            );
        } else {
            $this->logger->debug(
                sprintf('Resolved object "%s" was found in specification.', \get_class($object)),
                ['path' => $pointer->getPath()]
            );
        }

        return $object;
    }

    private function createReferencePointer(string $reference): SpecificationPointer
    {
        $reference = ltrim($reference, '#/');
        $referenceElements = explode('/', $reference);

        foreach ($referenceElements as $key => $element) {
            $referenceElements[$key] = str_replace(['~0', '~1'], ['~', '/'], $element);
        }

        return new SpecificationPointer($referenceElements);
    }

    private function validateReference(string $reference, SpecificationPointer $pointer): void
    {
        $referencePointer = $pointer->withPathElement('$ref');

        if ($reference === '') {
            throw new ParsingException('reference cannot be empty', $referencePointer);
        }

        if (strpos($reference, '#/') !== 0) {
            throw new ParsingException('only local references is supported - reference must start with "#/"', $referencePointer);
        }
    }
}
