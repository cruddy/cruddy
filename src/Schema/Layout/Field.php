<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Field extends Element {

    /**
     * The id of the field.
     */
    protected $id;

    /**
     * Init a field.
     *
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function modelClass()
    {
        return 'Cruddy.Layout.Field';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [ 'field' => $this->id ] + parent::toArray();
    }

}