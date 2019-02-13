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

use App\OpenAPI\SpecificationObjectMarkerInterface;
use App\Utility\AbstractClassCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockResponseCollection extends AbstractClassCollection implements SpecificationObjectMarkerInterface
{
    protected function getElementClassName(): string
    {
        return MockResponse::class;
    }
}
