<?php

namespace Kalnoy\Cruddy\Schema\Filters\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\Filters\BaseFilter;
use Kalnoy\Cruddy\Contracts\Field;

class Proxy extends BaseFilter
{
    /**
     * @var Filter|Field
     */
    private $field;

    /**
     * @var string
     */
    protected $fieldId;

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Filters.Proxy';
    }

    /**
     * @param Entity $form
     * @param string $id
     * @param string $fieldId
     */
    public function __construct(Entity $form, $id, $fieldId = null)
    {
        parent::__construct($form, $id);

        $this->fieldId = $fieldId ?: $id;
    }

    /**
     * @param Builder $builder
     * @param $data
     *
     * @return void
     */
    public function applyFilterConstraint(Builder $builder, $data)
    {
        $this->getField()->applyFilterConstraint($builder, $data);
    }

    /**
     * Generate a label.
     *
     * @return string
     */
    protected function generateLabel()
    {
        return $this->translate('filters') ?: $this->getField()->getLabel();
    }

    /**
     * @return Field|Filter
     */
    public function getField()
    {
        if ( ! is_null($this->field)) {
            return $this->field;
        }

        $this->field = $this->form->getFields()->get($this->fieldId);

        if ( ! $this->field instanceof Filter) {
            throw new \RuntimeException("The field {$this->field->getFullyQualifiedId()} must implement Filter contract.");
        }

        return $this->field;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'field' => $this->getField()->getId(),

        ] + parent::toArray();
    }
}