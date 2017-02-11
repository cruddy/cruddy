<?php

namespace Kalnoy\Cruddy\Service;

use Kalnoy\Cruddy\Form\BaseForm;

/**
 * Class BaseCollection
 *
 * @package Kalnoy\Cruddy\Service
 */
class BaseCollection implements \IteratorAggregate
{
    /**
     * @var BaseForm
     */
    protected $owner;

    /**
     * @var BaseFactory
     */
    protected $factory;

    /**
     * @var array|BaseItem[]
     */
    protected $items = [];

    /**
     * BaseCollection constructor.
     *
     * @param $owner
     * @param BaseFactory $factory
     */
    public function __construct($owner, BaseFactory $factory)
    {
        $this->factory = $factory;
        $this->owner = $owner;
    }

    /**
     * @param BaseItem $item
     *
     * @throws \Exception
     */
    public function push(BaseItem $item)
    {
        if ($this->has($item)) {
            throw new \Exception("The item [{$item->getId()}] already exists.");
        }

        $this->items[$item->getId()] = $item;
    }

    /**
     * @param BaseItem|string $item
     *
     * @return bool
     */
    public function has($item)
    {
        if ($item instanceof BaseItem) {
            $item = $item->getId();
        }

        return isset($this->items[$item]);
    }

    /**
     * @param string $id
     *
     * @return BaseItem|null
     */
    public function get($id)
    {
        return isset($this->items[$id]) ? $this->items[$id] : null;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function remove($id)
    {
        unset($this->items[$id]);

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return array_values(array_map(function (BaseItem $item) {
            return array_filter($item->getConfig());
        }, $this->items));
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        array_unshift($arguments, $this->owner);

        $item = $this->factory->resolve($name, $arguments);

        $this->push($item);

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ( ! $this->has($name)) {
            throw new \InvalidArgumentException("Unknown item [{$name}].");
        }

        return $this->get($name);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}