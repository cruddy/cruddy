<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * File input field.
 * 
 * @since 1.0.0
 */
class File extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'File';

    /**
     * {@inheritdoc}
     */
    protected $type = 'file';

    /**
     * Whether there going to be a few files.
     *
     * @var bool
     */
    public $multiple = false;

    /**
     * What kind of files the field accepts.
     *
     * @var string
     */
    public $accepts;

    /**
     * {@inheritdoc}
     *
     * @return string[]|string
     */
    public function extract(Eloquent $model)
    {
        $value = parent::extract($model);

        if ($this->multiple) return is_array($value) ? $value : [];

        return $value === null ? '' : $value;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]|string
     */
    public function process($value)
    {
        if (empty($value)) return null;

        return $this->multiple ? (array)$value : $value;
    }

    /**
     * Set accepts attribute for the <input> element.
     *
     * @param stirng $value
     *
     * @return $this
     */
    public function accepts($value)
    {
        $this->accepts = $value;

        return $this;
    }

    /**
     * Set multiple value.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function many($value = true)
    {
        $this->multiple = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'multiple' => $this->multiple,
            'accepts' => $this->accepts,
            'unique' => true,

        ] + parent::toArray();
    }
}