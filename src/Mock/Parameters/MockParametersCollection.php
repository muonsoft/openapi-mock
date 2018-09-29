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

use App\Utility\AbstractClassCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class MockParametersCollection extends AbstractClassCollection
{
    protected function getElementClassName(): string
    {
        return MockParameters::class;
    }
}
