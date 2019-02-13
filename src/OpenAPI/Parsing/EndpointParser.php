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

    public function __construct(ContextualParserInterface $responseCollectionParser)
    {
        $this->responseCollectionParser = $responseCollectionParser;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $endpoint = new Endpoint();

        $responsesPointer = $pointer->withPathElement('responses');
        $endpoint->responses = $this->responseCollectionParser->parsePointedSchema($specification, $responsesPointer);

        return $endpoint;
    }
}
