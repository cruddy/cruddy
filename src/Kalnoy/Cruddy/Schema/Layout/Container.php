<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Container extends Element {

    /**
     * Inner items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Compile items.
     *
     * @return array
     */
    public function compileItems()
    {
        return array_map(function ($item)
        {
            return $item->compile();

        }, $this->items);
    }

    /**
     * Add an item.
     *
     * @param \Kalnoy\Cruddy\Schema\Layout\Element $item
     */
    public function add(Element $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        return [ 'items' => $this->compileItems() ] + parent::compile();
    }

}