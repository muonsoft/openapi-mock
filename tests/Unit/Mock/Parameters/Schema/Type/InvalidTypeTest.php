<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Parameters\Schema\Type;

use App\Mock\Exception\InvalidTypeException;
use App\Mock\Parameters\Schema\Type\InvalidType;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class InvalidTypeTest extends TestCase
{
    private const ERROR = 'error';

    /** @test */
    public function getError_errorText_errorTextReturned(): void
    {
        $type = new InvalidType(self::ERROR);

        $error = $type->getError();

        $this->assertSame(self::ERROR, $error);
    }

    /**
     * @test
     * @dataProvider methodAndParametersProvider
     */
    public function method_anyParameters_exceptionThrown(string $method, array $parameters): void
    {
        $type = new InvalidType(self::ERROR);

        $this->expectException(InvalidTypeException::class);

        call_user_func_array([$type, $method], $parameters);
    }

    public function methodAndParametersProvider(): \Iterator
    {
        yield ['isNullable', []];
        yield ['isReadOnly', []];
        yield ['isWriteOnly', []];
        yield ['setNullable', [true]];
        yield ['setReadOnly', [true]];
        yield ['setWriteOnly', [true]];
    }
}
