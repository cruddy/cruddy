<?php

namespace Kalnoy\Cruddy\Schema\Layout;

/**
 * @method $this    row(mixed $items)
 * @method $this    field(mixed $id)
 * @method $this    text(string $contents)
 * @method $this    fieldset(string $title, mixed $items)
 */
class Layout extends Container {

    /**
     * The default tab.
     *
     * @var TabPane
     */
    private $defaultTab;

    /**
     * @return string
     */
    public function modelClass()
    {
        return 'Cruddy.Layout.Layout';
    }

    /**
     * Add a tab.
     *
     * @param string $title
     * @param string|array|\Closure $items
     *
     * @return $this
     */
    public function tab($title, $items)
    {
        return $this->add(new TabPane($title, $items));
    }

    /**
     * @param Element $item
     *
     * @return bool
     */
    protected function canBeAdded(Element $item)
    {
        return $item instanceof TabPane;
    }

    /**
     * Get a default tab.
     *
     * @return TabPane
     */
    public function getDefaultTab()
    {
        if ($this->defaultTab === null)
        {
            $this->defaultTab = new TabPane;

            array_unshift($this->items, $this->defaultTab);
        }

        return $this->defaultTab;
    }

    /**
     * Pass methods to the default tab.
     */
    public function __call($method, $parameters)
    {
        call_user_func_array([ $this->getDefaultTab(), $method ], $parameters);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->itemsToArray();
    }

}