<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Illuminate\Support\Str;
use Kalnoy\Cruddy\Form\BaseForm;
use Kalnoy\Cruddy\Form\Fields\BaseField;

/**
 * Slug field type.
 *
 * The slug uses other field's value to generate own value.
 *
 * @package Kalnoy\Cruddy\Form\Fields
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
     * @var string
     */
    public $separator = '-';

    /**
     * @param BaseForm $owner
     * @param string $id
     * @param string|array|null $field
     */
    public function __construct(BaseForm $owner, $id, $field = null)
    {
        parent::__construct($owner, $id);

        $this->field = $field;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function separator($value)
    {
        $this->separator = $value;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function processValueBeforeValidating($value)
    {
        return empty($value) ? null : Str::slug($value, $this->getSeparator());
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Slug';
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'field' => $this->field,

        ] + parent::getConfig();
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }
}