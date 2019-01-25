<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Faker\Provider;

use App\Faker\Provider\Base64Provider;
use App\Tests\Utility\TestCase\FakerCaseTrait;
use PHPUnit\Framework\TestCase;

class Base64ProviderTest extends TestCase
{
    use FakerCaseTrait;

    private const DEFAULT_LENGTH = 2000;
    private const GENERATED_TEXT = 'generated_text';

    protected function setUp(): void
    {
        $this->setUpFaker();
    }

    /** @test */
    public function base64_defaultLength_base64encodedTextReturned(): void
    {
        $provider = new Base64Provider($this->faker);
        $this->givenFaker_method_returnsValue('text', self::GENERATED_TEXT);

        $value = $provider->base64();

        $this->assertFaker_method_wasCalledOnceWithParameter('text', self::DEFAULT_LENGTH);
        $this->assertSame(self::GENERATED_TEXT, base64_decode($value, true));
    }

    /**
     * @test
     * @dataProvider lengthProvider
     */
    public function base64_givenLength_textGeneratedWithLessLengthForCorrectFinalEncodedText(
        int $length,
        int $textLength
    ): void {
        $provider = new Base64Provider($this->faker);

        $provider->base64($length);

        $this->assertFaker_method_wasCalledOnceWithParameter('text', $textLength);
    }

    public function lengthProvider(): array
    {
        return [
            [0, 2000],
            [20, 2000],
            [21, 14],
            [60, 40],
        ];
    }
}
