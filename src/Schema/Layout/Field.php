<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Field extends Element {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Field';

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
     * {@inheritdoc}
     */
    public function compile()
    {
        return [ 'field' => $this->id ] + parent::compile();
    }

}