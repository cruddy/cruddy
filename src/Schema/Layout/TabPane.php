<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class TabPane extends FieldSet {

    /**
     * @return string
     */
    public function modelClass()
    {
        return 'Cruddy.Layout.TabPane';
    }

    /**
     * Add a fieldset.
     *
     * @param string  $title
     * @param string|array|\Closure $items
     *
     * @return $this
     */
    public function fieldset($title, $items)
    {
        return $this->add(new FieldSet($title, $items));
    }

}