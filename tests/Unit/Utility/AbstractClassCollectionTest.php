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
    /**
     * @test
     * @expectedException \DomainException
     * @expectedExceptionMessageRegExp /Collection element class .* does not exist/
     */
    public function construct_givenInvalidClassName_exceptionThrown(): void
    {
        new class([]) extends AbstractClassCollection {
            protected function getElementClassName(): string
            {
                return '';
            }
        };
    }

    /**
     * @test
     * @expectedException \DomainException
     * @expectedExceptionMessage Collection element must be instance of
     */
    public function construct_givenInvalidClassInstance_exceptionThrown(): void
    {
        new class(['']) extends AbstractClassCollection {
            protected function getElementClassName(): string
            {
                return self::class;
            }
        };
    }

    /**
     * @test
     * @expectedException \DomainException
     * @expectedExceptionMessage Collection element must be instance of
     */
    public function set_givenInvalidClassInstance_exceptionThrown(): void
    {
        $collection = $this->createCollection();

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

    /**
     * @test
     * @expectedException \DomainException
     * @expectedExceptionMessage Collection element must be instance of
     */
    public function add_givenInvalidClassInstance_exceptionThrown(): void
    {
        $collection = $this->createCollection();

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
