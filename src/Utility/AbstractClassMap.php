<?php
/*
 * This file is part of Swagger Mock.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Utility;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
abstract class AbstractClassMap extends ArrayCollection
{
    /** @var string */
    private $className;

    /**
     * @throws \DomainException
     */
    public function __construct(array $elements = [])
    {
        $this->className = $this->getElementClassName();

        if (!class_exists($this->className) && !interface_exists($this->className)) {
            throw new \DomainException(
                sprintf('Map element class "%s" does not exist', $this->className)
            );
        }

        foreach ($elements as $element) {
            $this->validateElement($element);
        }

        parent::__construct($elements);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \DomainException
     */
    public function set($key, $value): void
    {
        $this->validateElement($value);

        parent::set($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \DomainException
     */
    public function add($element): bool
    {
        throw new \DomainException('Operation "add" cannot be applied to map.');
    }

    /**
     * @throws \DomainException
     */
    public function merge(self $map): void
    {
        foreach ($map as $key => $value) {
            $this->set($key, $value);
        }
    }

    abstract protected function getElementClassName(): string;

    /**
     * @throws \DomainException
     */
    private function validateElement($value): void
    {
        if (!$value instanceof $this->className) {
            throw new \DomainException(sprintf('Map element must be instance of %s', $this->className));
        }
    }
}
