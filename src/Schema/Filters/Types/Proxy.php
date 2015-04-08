<?php

namespace Kalnoy\Cruddy\Schema\Filters\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Schema\Filters\BaseFilter;
use Kalnoy\Cruddy\Contracts\Field;

class Proxy extends BaseFilter {

    /**
     * @var Filter|Field
     */
    protected $field;

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Filters.Proxy';
    }

    /**
     * @param Entity $entity
     * @param string $id
     * @param Field $field
     */
    public function __construct(Entity $entity, $id, Field $field)
    {
        parent::__construct($entity, $id);

        if ( ! $field instanceof Filter)
        {
            throw new \RuntimeException("The field {$field->getFullyQualifiedId()} must implement Filter contract.");
        }

        $this->field = $field;
    }

    /**
     * @param Builder $builder
     * @param $data
     *
     * @return void
     */
    public function applyFilterConstraint(Builder $builder, $data)
    {
        $this->field->applyFilterConstraint($builder, $data);
    }

    /**
     * Generate a label.
     *
     * @return string
     */
    protected function generateLabel()
    {
        return $this->translate('filters') ?: $this->field->getLabel();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'field' => $this->field->getId(),

        ] + parent::toArray();
    }
}