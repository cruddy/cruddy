<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class TabPane extends Fieldset {

    /**
     * {@inheritdoc}
     */
    protected $class = 'tab';

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
        return $this->add(new Fieldset($title, $items));
    }

}