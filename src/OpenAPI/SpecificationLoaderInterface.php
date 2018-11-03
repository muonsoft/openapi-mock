<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\OpenAPI;

use App\Mock\Parameters\MockParametersCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
interface SpecificationLoaderInterface
{
    public function loadMockParameters(string $url): MockParametersCollection;
}
