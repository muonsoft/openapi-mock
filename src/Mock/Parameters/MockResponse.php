<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Parameters;

use App\Mock\Parameters\Schema\SchemaMap;
use App\OpenAPI\SpecificationObjectMarkerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockResponse implements SpecificationObjectMarkerInterface
{
    public const DEFAULT_STATUS_CODE = Response::HTTP_INTERNAL_SERVER_ERROR;

    /** @var int */
    public $statusCode = self::DEFAULT_STATUS_CODE;

    /** @var SchemaMap */
    public $content;

    public function __construct()
    {
        $this->content = new SchemaMap();
    }
}
