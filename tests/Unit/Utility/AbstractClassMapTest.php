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

use App\Tests\Utility\Dummy\DummyClass;
use App\Utility\AbstractClassMap;
use PHPUnit\Framework\TestCase;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class AbstractClassMapTest extends TestCase
{
    /** @test */
    public function construct_givenInvalidClassName_exceptionThrown(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageRegExp('/Map element class .* does not exist/');

        new class([]) extends AbstractClassMap {
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
        $this->expectExceptionMessage('Map element must be instance of');

        new class(['']) extends AbstractClassMap {
            protected function getElementClassName(): string
            {
                return self::class;
            }
        };
    }

    /** @test */
    public function set_givenInvalidClassInstance_exceptionThrown(): void
    {
        $map = $this->createMap();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Map element must be instance of');

        $map->set(0, '');
    }

    /** @test */
    public function set_givenValidClassInstance_classInMap(): void
    {
        $map = $this->createMap();

        $map->set(0, new DummyClass());

        $this->assertCount(1, $map);
        $this->assertInstanceOf(DummyClass::class, $map->first());
    }

    /** @test */
    public function add_givenValidClassInstance_classInMap(): void
    {
        $map = $this->createMap();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Operation "add" cannot be applied to map.');

        $map->add(new DummyClass());
    }

    /** @test */
    public function merge_givenMap_collectionMergedWithGivenMap(): void
    {
        $map = $this->createMap();
        $map->set('first', new DummyClass());
        $mergingMap = $this->createMap();
        $mergingMap->set('second', new DummyClass());

        $map->merge($mergingMap);

        $this->assertCount(2, $map);
        $this->assertTrue($map->containsKey('first'));
        $this->assertTrue($map->containsKey('second'));
    }

    private function createMap(): AbstractClassMap
    {
        return new class([]) extends AbstractClassMap {
            protected function getElementClassName(): string
            {
                return DummyClass::class;
            }
        };
    }
}
