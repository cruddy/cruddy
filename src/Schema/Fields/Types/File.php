<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * File input field.
 *
 * @property string $accepts
 * @method $this accepts(StringField $value)
 *
 * @property bool $many
 * @method $this many(bool $value = true)
 *
 * @since 1.0.0
 */
class File extends BaseField {

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.File';
    }

    /**
     * @return string
     */
    protected function defaultAccepts()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]|string
     */
    public function extract($model)
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
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'multiple' => $this->many,
            'accepts' => $this->get('accepts', $this->defaultAccepts()),
            'unique' => true,

        ] + parent::toArray();
    }
}