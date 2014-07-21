<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Element {

    /**
     * The element class.
     */
    protected $class;

    /**
     * Compile an element.
     *
     * @return array
     */
    public function compile()
    {
        return [ 'method' => $this->class ];
    }

}