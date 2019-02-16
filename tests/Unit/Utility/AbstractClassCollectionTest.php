<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Unit\Utility;

use App\Tests\Utility\Dummy\DummyClass;
use App\Utility\AbstractClassCollection;
use PHPUnit\Framework\TestCase;

class AbstractClassCollectionTest extends TestCase
{
    /** @test */
    public function construct_givenInvalidClassName_exceptionThrown(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageRegExp('/Collection element class .* does not exist/');

        new class([]) extends AbstractClassCollection {
            protected function getElementClassName(): string
            {
                return '';
            }
        };
    }

    /** @test */
    public function construct_givenInvalidClassInstance_exceptionThrown(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Collection element must be instance of');

        new class(['']) extends AbstractClassCollection {
            protected function getElementClassName(): string
            {
                return self::class;
            }
        };
    }

    /** @test */
    public function set_givenInvalidClassInstance_exceptionThrown(): void
    {
        $collection = $this->createCollection();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Collection element must be instance of');

        $collection->set(0, '');
    }

    /** @test */
    public function set_givenValidClassInstance_classInCollection(): void
    {
        $collection = $this->createCollection();

        $collection->set(0, new DummyClass());

        $this->assertCount(1, $collection);
        $this->assertInstanceOf(DummyClass::class, $collection->first());
    }

    /** @test */
    public function add_givenInvalidClassInstance_exceptionThrown(): void
    {
        $collection = $this->createCollection();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Collection element must be instance of');

        $collection->add('');
    }

    /** @test */
    public function add_givenValidClassInstance_classInCollection(): void
    {
        $collection = $this->createCollection();

        $collection->add(new DummyClass());

        $this->assertCount(1, $collection);
        $this->assertInstanceOf(DummyClass::class, $collection->first());
    }

    /** @test */
    public function append_givenCollection_collectionAppendedToGivenCollection(): void
    {
        $collection = $this->createCollection();
        $collection->add(new DummyClass());
        $appendingCollection = $this->createCollection();
        $appendingCollection->add(new DummyClass());

        $collection->append($appendingCollection);

        $this->assertCount(2, $collection);
    }

    private function createCollection(): AbstractClassCollection
    {
        return new class([]) extends AbstractClassCollection {
            protected function getElementClassName(): string
            {
                return DummyClass::class;
            }
        };
    }
}
