<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Mock\Generation\Value\Primitive;

use App\Mock\Generation\Value\Primitive\FakerStringGenerator;
use App\Mock\Parameters\Schema\Type\Primitive\StringType;
use App\Tests\Utility\TestCase\FakerCaseTrait;
use App\Tests\Utility\TestCase\ProbabilityTestCaseTrait;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class FakerStringGeneratorTest extends TestCase
{
    use FakerCaseTrait;
    use ProbabilityTestCaseTrait;

    private const ENUM_VALUE = 'enumValue';
    private const PATTERN = '^\d{3}-\d{2}-\d{4}$';
    private const MIN_LENGTH = 5;
    private const MAX_LENGTH = 100;
    private const DEFAULT_MAX_LENGTH = 200;
    private const FORMATTED_VALUE = 'formatted_value';

    protected function setUp(): void
    {
        $this->setUpFaker();
    }

    /** @test */
    public function generateValue_stringTypeWithNullableParameters_nullValueReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->nullable = true;
        $this->givenFaker_method_returnsValue('rangedText', '');

        $test = function () use ($generator, $type) {
            return $generator->generateValue($type);
        };

        $this->expectClosureOccasionallyReturnsNull($test);
    }

    /** @test */
    public function generateValue_stringTypeWithEnum_enumValueReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->enum->add(self::ENUM_VALUE);

        $value = $generator->generateValue($type);

        $this->assertSame(self::ENUM_VALUE, $value);
    }

    /** @test */
    public function generateValue_stringTypeWithPattern_valueMatchesPatternReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->pattern = self::PATTERN;
        $faker = Factory::create();
        $faker->seed(0);
        $regexValue = $faker->regexify(self::PATTERN);
        $this->givenFaker_method_returnsValue('regexify', $regexValue);

        $value = $generator->generateValue($type);

        $this->assertFaker_method_wasCalledOnceWithParameter('regexify', self::PATTERN);
        $this->assertRegExp('/'.self::PATTERN.'/', $value);
    }

    /** @test */
    public function generateValue_stringTypeWithDateFormat_dateReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->format = 'date';
        $dateTime = \DateTime::createFromFormat(\DateTime::ATOM, '2017-07-21T17:32:28Z');
        $this->givenFaker_method_returnsValue('date', $dateTime);

        $value = $generator->generateValue($type);

        $this->assertFaker_method_wasCalledOnce('date');
        $this->assertSame('2017-07-21', $value);
    }

    /** @test */
    public function generateValue_stringTypeWithDateTimeFormat_dateTimeReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->format = 'date-time';
        $dateTime = \DateTime::createFromFormat(\DateTime::ATOM, '2017-07-21T17:32:28Z');
        $this->givenFaker_method_returnsValue('dateTime', $dateTime);

        $value = $generator->generateValue($type);

        $this->assertFaker_method_wasCalledOnce('dateTime');
        $this->assertSame('2017-07-21T17:32:28+00:00', $value);
    }

    /** @test */
    public function generateValue_stringTypeWithByteFormat_base64EncodedValueReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->format = 'byte';
        $type->maxLength = self::MAX_LENGTH;
        $this->givenFaker_method_returnsValue('base64', self::FORMATTED_VALUE);

        $value = $generator->generateValue($type);

        $this->assertFaker_method_wasCalledOnceWithParameter('base64', self::MAX_LENGTH);
        $this->assertSame(self::FORMATTED_VALUE, $value);
    }

    /**
     * @test
     * @dataProvider formatAndFakerMethodProvider
     */
    public function generateValue_stringTypeWithFormat_valueOfGivenFormatReturned(
        string $format,
        string $fakerMethod
    ): void {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->format = $format;
        $this->givenFaker_method_returnsValue($fakerMethod, self::FORMATTED_VALUE);

        $value = $generator->generateValue($type);

        $this->assertFaker_method_wasCalledOnce($fakerMethod);
        $this->assertSame(self::FORMATTED_VALUE, $value);
    }

    public function formatAndFakerMethodProvider(): array
    {
        return [
            ['email', 'email'],
            ['uuid', 'uuid'],
            ['uri', 'url'],
            ['hostname', 'domainName'],
            ['ipv4', 'ipv4'],
            ['ipv6', 'ipv6'],
        ];
    }

    /** @test */
    public function generateValue_stringTypeWithLengthParameters_textValueReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $type->minLength = self::MIN_LENGTH;
        $type->maxLength = self::MAX_LENGTH;
        $this->givenFaker_method_returnsValue('rangedText', self::FORMATTED_VALUE);

        $value = $generator->generateValue($type);

        $this->assertFaker_method_wasCalledOnceWithTwoParameters('rangedText', self::MIN_LENGTH, self::MAX_LENGTH);
        $this->assertSame(self::FORMATTED_VALUE, $value);
    }

    /** @test */
    public function generateValue_stringTypeWithoutParameters_textValueReturned(): void
    {
        $generator = $this->createFakerStringGenerator();
        $type = new StringType();
        $this->givenFaker_method_returnsValue('rangedText', self::FORMATTED_VALUE);

        $value = $generator->generateValue($type);

        $this->assertFaker_method_wasCalledOnceWithTwoParameters('rangedText', 0, self::DEFAULT_MAX_LENGTH);
        $this->assertSame(self::FORMATTED_VALUE, $value);
    }

    private function createFakerStringGenerator(): FakerStringGenerator
    {
        return new FakerStringGenerator($this->faker);
    }
}
