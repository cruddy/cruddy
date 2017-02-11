<?php

namespace Kalnoy\Cruddy\Form\Layout;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class Element
 *
 * @package Kalnoy\Cruddy\Form\Layout
 */
abstract class Element
{
    /**
     * @return string
     */
    abstract public function modelClass();

    /**
     * Compile an element.
     *
     * @return array
     */
    public function getConfig()
    {
        return [ 'class' => $this->modelClass() ];
    }

}