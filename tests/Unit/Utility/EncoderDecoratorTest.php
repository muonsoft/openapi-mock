<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Utility;

use App\Utility\EncoderDecorator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class EncoderDecoratorTest extends TestCase
{
    private const FORMAT = 'format';
    private const JSON = 'json';
    private const CONTEXT = ['context'];

    /** @var EncoderInterface */
    private $decorated;

    protected function setUp(): void
    {
        $this->decorated = \Phake::mock(EncoderInterface::class);
    }

    /** @test */
    public function encode_json_valueEncodedByDecoratedServiceDirectly(): void
    {
        $encoder = $this->createEncoderDecorator();
        $data = new \stdClass();
        $data->key = 'value';
        $value = $this->givenDecoratedEncoder_encode_returnsValue();

        $actualValue = $encoder->encode($data, self::JSON, self::CONTEXT);

        $this->assertDecoratedEncoder_encode_wasCalledOnceWithDataAndFormatAndContext($data, self::JSON, self::CONTEXT);
        $this->assertSame($value, $actualValue);
    }

    /** @test */
    public function encode_notAJson_valueConvertedToArrayAndEncodedByDecoratedService(): void
    {
        $encoder = $this->createEncoderDecorator();
        $data = new \stdClass();
        $data->key = 'value';
        $value = $this->givenDecoratedEncoder_encode_returnsValue();

        $actualValue = $encoder->encode($data, self::FORMAT, self::CONTEXT);

        $this->assertDecoratedEncoder_encode_wasCalledOnceWithDataAndFormatAndContext(['key' => 'value'], self::FORMAT, self::CONTEXT);
        $this->assertSame($value, $actualValue);
    }

    /** @test */
    public function supportsEncoding_format_decoratedResultReturned(): void
    {
        $encoder = $this->createEncoderDecorator();
        $this->givenDecoratedEncoder_supportEncoding_returnsTrue();

        $supports = $encoder->supportsEncoding(self::FORMAT);

        $this->assertDecoratedEncoder_supportsEncoding_wasCalledOnceWithFormat();
        $this->assertTrue($supports);
    }

    private function assertDecoratedEncoder_supportsEncoding_wasCalledOnceWithFormat(): void
    {
        \Phake::verify($this->decorated)
            ->supportsEncoding(self::FORMAT);
    }

    private function givenDecoratedEncoder_supportEncoding_returnsTrue(): void
    {
        \Phake::when($this->decorated)
            ->supportsEncoding(\Phake::anyParameters())
            ->thenReturn(true);
    }

    private function createEncoderDecorator(): EncoderDecorator
    {
        return new EncoderDecorator($this->decorated);
    }

    private function assertDecoratedEncoder_encode_wasCalledOnceWithDataAndFormatAndContext(
        $data,
        string $format,
        array $context
    ): void {
        \Phake::verify($this->decorated)
            ->encode($data, $format, $context);
    }

    private function givenDecoratedEncoder_encode_returnsValue(): string
    {
        $value = 'value';

        \Phake::when($this->decorated)
            ->encode(\Phake::anyParameters())
            ->thenReturn($value);

        return $value;
    }
}
