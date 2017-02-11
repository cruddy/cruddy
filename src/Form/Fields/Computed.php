<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Kalnoy\Cruddy\Form\BaseForm;

/**
 * Computed field.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Computed extends BaseField
{    
    /**
     * @param BaseForm $owner
     * @param string $id
     * @param callback $getter
     */
    public function __construct(BaseForm $owner, $id, callable $getter)
    {
        parent::__construct($owner, $id);

        $this->getter($getter);
    }

    /**
     * @inheritdoc
     */
    public function setModelValue($model, $value)
    {
        // void
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Computed';
    }
}