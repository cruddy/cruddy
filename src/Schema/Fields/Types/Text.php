<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Base text field that is represented with textarea.
 *
 * @method $this rows(int $count)
 * @property int $rows
 *
 * @since 1.0.0
 */
class Text extends BaseField {

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Text';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [ 'rows' => $this->get('rows', 3) ]  + parent::toArray();
    }
}