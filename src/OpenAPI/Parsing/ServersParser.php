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

use App\Mock\Parameters\Servers;
use App\OpenAPI\ErrorHandling\ErrorHandlerInterface;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ServersParser implements ParserInterface
{
    /** @var ErrorHandlerInterface */
    private $errorHandler;

    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        $servers = new Servers();

        $serversSchema = $specification->getSchema($pointer);
        $isValid = $this->validateServersSchema($serversSchema, $pointer);

        if ($isValid) {
            foreach ($serversSchema as $index => $serverSchema) {
                $isValid = $this->validateServerSchema($index, $serverSchema, $pointer);

                if ($isValid) {
                    $servers->urls->add($serverSchema['url']);
                }
            }
        }

        return $servers;
    }

    private function validateServersSchema($serversSchema, SpecificationPointer $pointer): bool
    {
        $isValid = is_array($serversSchema);

        if (!$isValid) {
            $this->errorHandler->reportError('Servers schema is invalid and will be ignored', $pointer);
        }

        return $isValid;
    }

    private function validateServerSchema($index, $serverSchema, SpecificationPointer $pointer): bool
    {
        $isValid = is_array($serverSchema) && array_key_exists('url', $serverSchema);

        if (!$isValid) {
            $this->errorHandler->reportError(
                'Server schema is invalid and will be ignored',
                $pointer->withPathElement($index)
            );
        }

        return $isValid;
    }
}
