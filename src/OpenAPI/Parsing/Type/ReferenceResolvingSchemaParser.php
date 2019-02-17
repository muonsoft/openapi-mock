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
use App\OpenAPI\Parsing\ReferenceResolvingParser;
use App\OpenAPI\Parsing\SpecificationAccessor;
use App\OpenAPI\Parsing\SpecificationPointer;
use App\OpenAPI\SpecificationObjectMarkerInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ReferenceResolvingSchemaParser implements ParserInterface
{
    /** @var ParserInterface */
    private $delegatingSchemaParser;

    /** @var ReferenceResolvingParser */
    private $resolvingParser;

    public function __construct(ParserInterface $delegatingSchemaParser, ReferenceResolvingParser $resolvingParser)
    {
        $this->delegatingSchemaParser = $delegatingSchemaParser;
        $this->resolvingParser = $resolvingParser;
    }

    public function parsePointedSchema(SpecificationAccessor $specification, SpecificationPointer $pointer): SpecificationObjectMarkerInterface
    {
        return $this->resolvingParser->resolveReferenceAndParsePointedSchema($specification, $pointer, $this->delegatingSchemaParser);
    }
}
