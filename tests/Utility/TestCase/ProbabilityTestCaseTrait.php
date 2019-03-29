<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Utility\TestCase;

use PHPUnit\Framework\Assert;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
trait ProbabilityTestCaseTrait
{
    protected function expectClosureOccasionallyReturnsNull(\Closure $test): void
    {
        for ($i = 0; $i < 100; $i++) {
            $value = $test();

            if (null === $value) {
                break;
            }
        }

        Assert::assertNull($value);
    }

    protected function expectClosureOccasionallyReturnsValueGreaterThan(\Closure $test, $expectedValue): void
    {
        for ($i = 0; $i < 100; $i++) {
            $value = $test();

            if ($value > $expectedValue) {
                break;
            }
        }

        Assert::assertGreaterThan($expectedValue, $value);
    }
}
