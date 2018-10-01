<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock;

use App\Mock\Parameters\MockParameters;
use App\Mock\Parameters\MockParametersCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockParametersRepository
{
    /** @var MockParametersCollection */
    private $collection;

    public function __construct(MockParametersCollection $collection)
    {
        $this->collection = $collection;
    }

    public function findMockParameters(string $httpMethod, string $uri): MockParameters
    {

    }
}
