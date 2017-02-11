<?php

namespace Kalnoy\Cruddy\Form\Layout;

/**
 * Class Container
 *
 * @package Kalnoy\Cruddy\Form\Layout
 */
abstract class Container extends Element implements \Countable,
                                                       \IteratorAggregate
{
    /**
     * Inner items.
     *
     * @var array
     */
    protected $items = [ ];

    /**
     * Compile items.
     *
     * @return array
     */
    public function itemsToArray()
    {
        return array_map(function (Element $item) {
            return $item->getConfig();
        }, $this->items);
    }

    /**
     * Add an item.
     *
     * @param Element $item
     *
     * @return $this
     */
    public function add(Element $item)
    {
        if ( ! $this->canBeAdded($item)) {
            throw new \RuntimeException(
                'The element of type ['.get_class($item).'] cannot be added to '.
                'container of type ['.get_class($this).'].'
            );
        }

        $this->items[] = $item;

        return $this;
    }

    /**
     * @param Element $item
     *
     * @return bool
     */
    protected function canBeAdded(Element $item)
    {
        return ! $item instanceof Layout;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [ 'items' => $this->itemsToArray() ] + parent::getConfig();
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

}