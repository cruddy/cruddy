<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Form\Fields\BaseField;

/**
 * Base text field that is represented with text area.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Text extends BaseField
{
    /**
     * @var int
     */
    public $rows = 3;

    /**
     * Set the number of rows for the text area.
     * 
     * @param int $value
     *
     * @return $this
     */
    public function rows($value)
    {
        $this->rows = $value;
        
        return $this;
    }

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }
    
    /**
     * @inheritdoc
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Text';
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [ 'rows' => $this->getRows() ]  + parent::getConfig();
    }
}