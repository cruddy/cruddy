<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

class Text extends BaseTextField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'text';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $inputType = 'textarea';

    /**
     * The number of rows for a textarea.
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
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return ['rows' => $this->rows]  + parent::toArray();
    }
}