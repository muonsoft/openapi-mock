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
use Ramsey\Collection\AbstractCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EndpointParameterCollection extends AbstractCollection implements SpecificationObjectMarkerInterface
{
    public function getType(): string
    {
        return EndpointParameter::class;
    }

    public function unserialize($serialized): void
    {
        $this->data = unserialize($serialized);
    }
}
