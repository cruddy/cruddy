<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Base text field that is represented with textarea.
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
     * The number of rows for the textarea.
     *
     * @var int
     */
    public $rows = 3;

    /**
     * Set the number of rows.
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
     * {@inheritdoc}
     */
    public function toArray()
    {
        return ['rows' => $this->rows]  + parent::toArray();
    }
}