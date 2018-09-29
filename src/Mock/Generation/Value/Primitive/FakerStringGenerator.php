<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation\Value\Primitive;

use App\Mock\Generation\Value\ValueGeneratorInterface;
use App\Mock\Parameters\Schema\Type\TypeMarkerInterface;
use Faker\Generator;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class FakerStringGenerator implements ValueGeneratorInterface
{
    /** @var Generator */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function generateValue(TypeMarkerInterface $type): ?string
    {
        return $this->faker->sentence();
    }
}
