<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Mock\Generation;

use App\Mock\Parameters\Schema\Schema;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class DataGenerator
{
    /** @var ValueGeneratorLocator */
    private $generatorLocator;

    public function __construct(ValueGeneratorLocator $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    /**
     * @param Schema $schema
     * @return mixed
     */
    public function generateData(Schema $schema)
    {
        $valueGenerator = $this->generatorLocator->getValueGenerator($schema->value);

        return $valueGenerator->generateValue($schema->value);
    }
}
