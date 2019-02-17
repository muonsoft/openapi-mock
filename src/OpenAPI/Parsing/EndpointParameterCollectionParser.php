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

use App\Enum\EndpointParameterLocationEnum;
use App\Mock\Parameters\EndpointParameter;
use App\Mock\Parameters\EndpointParameterCollection;
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParameterCollectionParser implements ParserInterface
{
    /** @var ParserInterface */
    private $resolvingSchemaParser;

    /** @var ErrorHandlerInterface */
    private $errorHandler;

    public function __construct(ParserInterface $resolvingSchemaParser, ErrorHandlerInterface $errorHandler)
    {
        $this->resolvingSchemaParser = $resolvingSchemaParser;
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $parameters = new EndpointParameterCollection();

        $rawParameters = $specification->getSchema($pointer);

        foreach ($rawParameters as $index => $rawParameter) {
            $parameterPointer = $pointer->withPathElement($index);
            $isValid = $this->validateEndpointParameter($rawParameter, $parameterPointer);

            if ($isValid) {
                $parameter = $this->parseEndpointParameter($specification, $rawParameter, $parameterPointer);
                $parameters->add($parameter);
            }
        }

        return $parameters;
    }

    private function validateEndpointParameter($rawParameter, SpecificationPointer $parameterPointer): bool
    {
        $error = null;

        foreach ($this->assertionRulesAndFormatters() as $ruleAndFormatter) {
            [$rule, $formatter] = $ruleAndFormatter;

            if ($rule($rawParameter)) {
                $error = $formatter($rawParameter);

                break;
            }
        }

        if ($error) {
            $this->errorHandler->reportError($error, $parameterPointer);
        }

        return null === $error;
    }

    private function parseEndpointParameter(
        SpecificationAccessor $specification,
        array $rawParameter,
        SpecificationPointer $parameterPointer
    ): EndpointParameter {
        $parameter = new EndpointParameter();
        $parameter->name = (string) $rawParameter['name'];
        $parameter->in = new EndpointParameterLocationEnum(strtolower($rawParameter['in']));
        $parameter->required = (bool) ($rawParameter['required'] ?? false);

        if ($this->pathParameterIsNotRequired($parameter)) {
            $this->errorHandler->reportWarning('Path parameter must be required', $parameterPointer);
            $parameter->required = true;
        }

        $parameterSchemaPointer = $parameterPointer->withPathElement('schema');
        $parameter->schema = $this->resolvingSchemaParser->parsePointedSchema($specification, $parameterSchemaPointer);

        return $parameter;
    }

    private function pathParameterIsNotRequired(EndpointParameter $parameter): bool
    {
        return !$parameter->required && EndpointParameterLocationEnum::PATH === $parameter->in->getValue();
    }

    private function assertionRulesAndFormatters(): \Iterator
    {
        yield [
            function ($rawParameter): bool {
                return !is_array($rawParameter);
            },
            function (): string {
                return 'Invalid parameter schema.';
            }
        ];
        yield [
            function (array $rawParameter): bool {
                return !array_key_exists('name', $rawParameter) || !is_string($rawParameter['name']);
            },
            function (): string {
                return 'Parameter must have name of string format';
            }
        ];
        yield [
            function (array $rawParameter): bool {
                return !array_key_exists('in', $rawParameter) || !is_string($rawParameter['in']);
            },
            function (): string {
                return 'Parameter location (tag "in") is not present or has invalid type';
            }
        ];
        yield [
            function (array $rawParameter): bool {
                return !EndpointParameterLocationEnum::isValid(strtolower($rawParameter['in']));
            },
            function (array $rawParameter): string {
                return sprintf(
                    'Invalid parameter location "%s". Must be one of: %s.',
                    $rawParameter['in'],
                    implode(', ', EndpointParameterLocationEnum::toArray())
                );
            }
        ];
        yield [
            function (array $rawParameter): bool {
                return !array_key_exists('schema', $rawParameter);
            },
            function (): string {
                return 'Only parameters with "schema" tag are currently supported.';
            }
        ];
        yield [
            function (array $rawParameter): bool {
                return !is_array($rawParameter['schema']);
            },
            function (): string {
                return 'Invalid schema provider for parameter.';
            }
        ];
    }
}
