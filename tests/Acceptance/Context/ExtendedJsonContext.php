<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Acceptance\Context;

use Behatch\Context\JsonContext;
use PHPUnit\Framework\Assert;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ExtendedJsonContext extends JsonContext
{
    /**
     * @Then the JSON node :node should be a number in range between :minimum and :maximum
     */
    public function theJsonNodeShouldBeANumberInRangeBetween($node, float $minimum, float $maximum): void
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        Assert::assertInternalType('numeric', $actual);
        Assert::assertGreaterThanOrEqual($minimum, $actual);
        Assert::assertLessThanOrEqual($maximum, $actual);
    }

    /**
     * @Then the JSON node :node should be a string with length in range between :minimum and :maximum
     */
    public function theJsonNodeShouldBeAStringWithLengthInRangeBetween($node, float $minimum, float $maximum): void
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        Assert::assertInternalType('string', $actual);
        Assert::assertGreaterThanOrEqual($minimum, \strlen($actual));
        Assert::assertLessThanOrEqual($maximum, \strlen($actual));
    }

    /**
     * @Then the JSON node :node should match format :format
     */
    public function theJsonNodeShouldMatchFormat($node, string $format): void
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        Assert::assertStringMatchesFormat($format, $actual);
    }
}
