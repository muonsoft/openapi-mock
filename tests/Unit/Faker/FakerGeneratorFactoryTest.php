<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Unit\Faker;

use App\Faker\FakerGeneratorFactory;
use App\Faker\Provider\Base64Provider;
use App\Faker\Provider\TextProvider;
use Faker\Generator;
use PHPUnit\Framework\TestCase;

class FakerGeneratorFactoryTest extends TestCase
{
    /** @test */
    public function createGenerator_noParameters_fakerGeneratorCreatedAndReturned(): void
    {
        $generator = FakerGeneratorFactory::createGenerator();

        $this->assertNotNull($generator);
        $this->assertGeneratorHasProviderClass($generator, Base64Provider::class);
        $this->assertGeneratorHasProviderClass($generator, TextProvider::class);
    }

    private function assertGeneratorHasProviderClass(Generator $generator, string $class): void
    {
        $providers = $generator->getProviders();

        $contains = false;
        foreach ($providers as $provider) {
            if (\get_class($provider) === $class) {
                $contains = true;

                break;
            }
        }

        $this->assertTrue($contains, sprintf('Generator has provider "%s".', $class));
    }
}
