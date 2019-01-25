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
abstract class AbstractClassCollection extends ArrayCollection
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
                sprintf('Collection element class "%s" does not exist', $this->className)
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
        $this->validateElement($element);

        return parent::add($element);
    }

    abstract protected function getElementClassName(): string;

    /**
     * @throws \DomainException
     */
    private function validateElement($value): void
    {
        if (!$value instanceof $this->className) {
            throw new \DomainException(sprintf('Collection element must be instance of %s', $this->className));
        }
    }
}
