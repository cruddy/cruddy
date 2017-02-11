<?php

namespace Kalnoy\Cruddy\Form\Layout;

/**
 * Class Field
 *
 * @package Kalnoy\Cruddy\Form\Layout
 */
class Field extends Element
{
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
     * @inheritdoc
     */
    public function getConfig()
    {
        return [ 'field' => $this->id ] + parent::getConfig();
    }

}