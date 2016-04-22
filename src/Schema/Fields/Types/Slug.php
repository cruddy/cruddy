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
     * @param BaseForm $form
     * @param string $id
     * @param string|array|null $field
     */
    public function __construct(BaseForm $form, $id, $field = null)
    {
        parent::__construct($form, $id);

        $this->field = $field;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function parseInputValue($value)
    {
        return empty($value) ? null : str_slug($value, $this->getSeparator());
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
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

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->get('separator', '-');
    }
}