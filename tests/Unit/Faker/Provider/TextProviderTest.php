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

use App\Faker\Provider\TextProvider;
use App\Tests\Utility\TestCase\FakerCaseTrait;
use PHPUnit\Framework\TestCase;

class TextProviderTest extends TestCase
{
    use FakerCaseTrait;

    private const GENERATED_VALUE = 'generated_value';

    private const LEXIFY_VALUE = 'lexify_value';

    protected function setUp(): void
    {
        $this->setUpFaker();
    }

    /**
     * @test
     * @dataProvider lengthAndLexifyStringProvider
     */
    public function rangedText_maxLengthIsLessThan5_lexifyGeneratorUsed(
        int $length,
        string $lexifyString
    ): void {
        $provider = new TextProvider($this->faker);
        $this->givenFaker_method_returnsValue('lexify', self::GENERATED_VALUE);

        $value = $provider->rangedText(0, $length);

        $this->assertFaker_method_wasCalledOnceWithParameter('lexify', $lexifyString);
        $this->assertSame(self::GENERATED_VALUE, $value);
    }

    public function lengthAndLexifyStringProvider(): array
    {
        return [
            [4, '????'],
            [3, '???'],
        ];
    }

    /** @test */
    public function rangedText_maxLengthAndTextGenerated_textReturned(): void {
        $provider = new TextProvider($this->faker);
        $this->givenFaker_method_returnsValue('text', self::GENERATED_VALUE);

        $value = $provider->rangedText(0, 10);

        $this->assertFaker_method_wasCalledOnceWithParameter('text', 10);
        $this->assertSame(self::GENERATED_VALUE, $value);
    }

    /** @test */
    public function rangedText_minLengthAndTextCannotBeGeneratedByTextGenerator_lexifyGeneratorUsedInstead(): void {
        $provider = new TextProvider($this->faker);
        $this->givenFaker_method_returnsValue('text', 'abc');
        $this->givenFaker_method_returnsValue('lexify', self::LEXIFY_VALUE);

        $value = $provider->rangedText(5, 10);

        $this->assertFaker_method_wasCalledOnceWithParameter('lexify', '?????');
        $this->assertSame(self::LEXIFY_VALUE, $value);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Max length cannot be less than min length
     */
    public function rangedText_invalidLengths_exceptionThrown(): void
    {
        $provider = new TextProvider($this->faker);

        $provider->rangedText(5, 0);
    }
}
