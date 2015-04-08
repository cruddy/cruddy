<?php

namespace Kalnoy\Cruddy\Schema\Layout;

use Illuminate\Contracts\Support\Arrayable;

abstract class Element implements Arrayable {

    /**
     * @return string
     */
    abstract public function modelClass();

    /**
     * Compile an element.
     *
     * @return array
     */
    public function toArray()
    {
        return [ 'class' => $this->modelClass() ];
    }

}