<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\BaseForm;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Slug field type.
 *
 * The slug uses other field's value to generate own value.
 *
 * @property string $separator
 * @method $this separator(StringField $char)
 *
 * @since 1.0.0
 */
class Slug extends BaseField
{
    /**
     * The id or array of reference field with which slug will be linked.
     *
     * @var string|array
     */
    protected $field;

    /**
     * @param BaseForm $entity
     * @param string $id
     * @param string|array|null $field
     */
    public function __construct(BaseForm $entity, $id, $field = null)
    {
        parent::__construct($entity, $id);

        $this->field = $field;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function process($value)
    {
        return empty($value) ? null : str_slug($value);
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Slug';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'field' => $this->field,

        ] + parent::toArray();
    }
}