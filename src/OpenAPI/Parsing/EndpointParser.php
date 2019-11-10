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

use App\Mock\Parameters\Endpoint;
use App\OpenAPI\Routing\UrlMatcherFactory;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParser implements ContextualParserInterface
{
    /** @var ParserInterface */
    private $responseCollectionParser;

    /** @var ParserInterface */
    private $parameterCollectionParser;

    /** @var UrlMatcherFactory */
    private $urlMatcherFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ParserInterface $responseCollectionParser,
        ParserInterface $parameterCollectionParser,
        UrlMatcherFactory $urlMatcherFactory,
        LoggerInterface $logger
    ) {
        $this->responseCollectionParser = $responseCollectionParser;
        $this->parameterCollectionParser = $parameterCollectionParser;
        $this->urlMatcherFactory = $urlMatcherFactory;
        $this->logger = $logger;
    }

    public function parsePointedSchema(
        SpecificationAccessor $specification,
        SpecificationPointer $pointer,
        ContextMarkerInterface $context
    ): SpecificationObjectMarkerInterface {
        assert($context instanceof EndpointContext);

        $endpoint = new Endpoint();
        $endpoint->path = $context->path;
        $endpoint->httpMethod = $context->httpMethod;

        $responsesPointer = $pointer->withPathElement('responses');
        $endpoint->responses = $this->responseCollectionParser->parsePointedSchema($specification, $responsesPointer);

        $parametersPointer = $pointer->withPathElement('parameters');
        $endpoint->parameters = $this->parameterCollectionParser->parsePointedSchema($specification, $parametersPointer);
        $endpoint->parameters = $endpoint->parameters->merge($context->parameters);

        $endpoint->urlMatcher = $this->urlMatcherFactory->createUrlMatcher($endpoint, $pointer);

        $this->logger->debug(
            sprintf(
                'Endpoint with method "%s" and path "%s" was successfully parsed.',
                $endpoint->httpMethod,
                $endpoint->path
            ),
            ['path' => $pointer->getPath()]
        );

        return $endpoint;
    }
}
