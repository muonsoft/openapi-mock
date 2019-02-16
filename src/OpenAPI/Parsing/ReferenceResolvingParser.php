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

use App\Mock\Parameters\Schema\Type\InvalidType;
use App\OpenAPI\Parsing\Error\ParsingErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ReferenceResolvingParser
{
    /** @var ParsingErrorHandlerInterface */
    private $errorHandler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(ParsingErrorHandlerInterface $errorHandler, LoggerInterface $logger)
    {
        $this->errorHandler = $errorHandler;
        $this->logger = $logger;
    }

    public function resolveReferenceAndParsePointedSchema(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ParserInterface $parser
    ): SpecificationObjectMarkerInterface {
        $schema = $specification->getSchema($pointer);

        if (array_key_exists('$ref', $schema)) {
            $reference = trim($schema['$ref']);
            $context = new ReferenceResolvingParserContext($specification, $pointer, $parser);
            $object = $this->parseReferencedSchema($reference, $context);
        } else {
            $object = $parser->parsePointedSchema($specification, $pointer);
        }

        return $object;
    }

    private function parseReferencedSchema(string $reference, ReferenceResolvingParserContext $context): SpecificationObjectMarkerInterface
    {
        $this->logger->debug(sprintf('Reference "%s" detected.', $reference), ['path' => $context->pointer->getPath()]);

        $error = $this->validateReference($reference, $context->pointer);

        if ($error) {
            $object = new InvalidType($error);
        } else {
            $object = $this->parseOrLoadResolvedObject($reference, $context);
        }

        return $object;
    }

    private function validateReference(string $reference, SpecificationPointer $pointer): ?string
    {
        $error = null;

        if ('' === $reference) {
            $error = 'reference cannot be empty';
        } elseif (0 !== strpos($reference, '#/')) {
            $error = 'only local references is supported - reference must start with "#/"';
        }

        if ($error) {
            $referencePointer = $pointer->withPathElement('$ref');
            $error = $this->errorHandler->reportError($error, $referencePointer);
        }

        return $error;
    }

    private function parseOrLoadResolvedObject(string $reference, ReferenceResolvingParserContext $context): SpecificationObjectMarkerInterface
    {
        $object = $context->specification->findResolvedObject($reference);

        if (null === $object) {
            $referencePointer = $this->createReferencePointer($reference);
            $object = $context->parser->parsePointedSchema($context->specification, $referencePointer);
            $context->specification->setResolvedObject($reference, $object);

            $this->logger->debug(
                sprintf('Object "%s" was resolved and set to specification.', \get_class($object)),
                [
                    'path'          => $context->pointer->getPath(),
                    'referencePath' => $referencePointer->getPath(),
                ]
            );
        } else {
            $this->logger->debug(
                sprintf('Resolved object "%s" was found in specification.', \get_class($object)),
                ['path' => $context->pointer->getPath()]
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
}
