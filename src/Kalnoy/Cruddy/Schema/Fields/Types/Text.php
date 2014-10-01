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
     * {@inheritdoc}
     */
    protected $type = 'text';

    /**
     * {@inheritdoc}
     */
    protected $class = 'Text';

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [ 'rows' => $this->get('rows', 3) ]  + parent::toArray();
    }
}