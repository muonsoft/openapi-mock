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

use App\Mock\Parameters\EndpointParameterCollection;
use App\Mock\Parameters\InvalidObject;
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParameterCollectionParser implements ParserInterface
{
    /** @var ParserInterface */
    private $parameterParser;

    /** @var ErrorHandlerInterface */
    private $errorHandler;

    public function __construct(ParserInterface $parameterParser, ErrorHandlerInterface $errorHandler)
    {
        $this->parameterParser = $parameterParser;
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $parameters = new EndpointParameterCollection();

        $rawParameters = $specification->getSchema($pointer);

        foreach (array_keys($rawParameters) as $index) {
            $parameterPointer = $pointer->withPathElement($index);
            $parameter = $this->parameterParser->parsePointedSchema($specification, $parameterPointer);

            if ($parameter instanceof InvalidObject) {
                $this->errorHandler->reportError($parameter->getError(), $parameterPointer);
            } else {
                $parameters->add($parameter);
            }
        }

        return $parameters;
    }
}
