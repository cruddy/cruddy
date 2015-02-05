<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * File input field.
 *
 * @property string $accepts
 * @method $this accepts(string $value)
 *
 * @since 1.0.0
 */
class File extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Cruddy.Fields.File';

    /**
     * {@inheritdoc}
     */
    protected $type = 'file';

    /**
     * The default value for "accepts".
     */
    protected static $defaultAccepts;

    /**
     * Whether there going to be a few files.
     *
     * @var bool
     */
    public $multiple = false;

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
        if (empty($value)) $value = null;

        return $this->multiple ? (array)$value : $value;
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
            'accepts' => $this->get('accepts', static::$defaultAccepts),
            'unique' => true,

        ] + parent::toArray();
    }
}