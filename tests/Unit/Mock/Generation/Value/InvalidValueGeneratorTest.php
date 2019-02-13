<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value;

use App\Mock\Generation\Value\InvalidValueGenerator;
use App\Mock\Parameters\Schema\Type\InvalidType;
use App\Tests\Utility\TestCase\LoggerTestCaseTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidValueGeneratorTest extends TestCase
{
    use LoggerTestCaseTrait;

    private const ERROR = 'error';

    protected function setUp(): void
    {
        $this->setUpLogger();
    }

    /** @test */
    public function generateValue_invalidType_nullReturnedAndErrorLogged(): void
    {
        $generator = new InvalidValueGenerator($this->logger);
        $type = new InvalidType(self::ERROR);

        $generator->generateValue($type);

        $this->assertLogger_warning_wasCalledOnce('Message generation was ignored due to invalid type with error: "error".');
    }
}
