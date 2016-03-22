<?php

namespace Kalnoy\Cruddy\Schema\Layout;

abstract class BaseFieldSet extends Container {

    /**
     * Init the container.
     *
     * @param string|array|\Callback $items
     */
    public function __construct($items = null)
    {
        if ($items instanceof \Closure)
        {
            $items($this);
        }
        elseif ($items !== null)
        {
            $this->field($items);
        }
    }

    /**
     * Add a field or a list of fields.
     *
     * @param string|array $id
     *
     * @return $this
     */
    public function field($id)
    {
        $id = is_array($id) ? $id : func_get_args();

        foreach ($id as $item)
        {
            is_array($item) ? $this->row($item) : $this->add(new Field($item));
        }

        return $this;
    }

    /**
     * Add a row.
     *
     * @param array|\Closure $items
     *
     * @return $this
     */
    public function row($items = null)
    {
        return $this->add(new Row($items));
    }

    /**
     * Add a text node.
     *
     * @param string $contents
     *
     * @return $this
     */
    public function text($contents)
    {
        return $this->add(new Text($contents));
    }

}