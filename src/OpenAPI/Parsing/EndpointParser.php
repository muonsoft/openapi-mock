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
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParser implements ContextualParserInterface
{
    /** @var ContextualParserInterface */
    private $responseCollectionParser;

    /** @var ContextualParserInterface */
    private $parameterCollectionParser;

    public function __construct(ContextualParserInterface $responseCollectionParser, ContextualParserInterface $parameterCollectionParser)
    {
        $this->responseCollectionParser = $responseCollectionParser;
        $this->parameterCollectionParser = $parameterCollectionParser;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $endpoint = new Endpoint();

        $responsesPointer = $pointer->withPathElement('responses');
        $endpoint->responses = $this->responseCollectionParser->parsePointedSchema($specification, $responsesPointer);

        $parametersPointer = $pointer->withPathElement('parameters');
        $endpoint->parameters = $this->parameterCollectionParser->parsePointedSchema($specification, $parametersPointer);

        return $endpoint;
    }
}
