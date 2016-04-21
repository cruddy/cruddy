<?php

namespace Kalnoy\Cruddy\Schema\Columns\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Columns\BaseColumn;
use Kalnoy\Cruddy\Contracts\Field;
use Kalnoy\Cruddy\Entity;

/**
 * Proxy relies on a field to do stuff. It just passes calls to the field.
 *
 * @since 1.0.0
 */
class Proxy extends BaseColumn
{
    /**
     * The field instance.
     *
     * @var \Kalnoy\Cruddy\Contracts\Field
     */
    protected $field;

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function getModelClass()
    {
        return 'Cruddy.Columns.Proxy';
    }

    /**
     * Init column.
     *
     * @param Entity $form
     * @param string $id
     * @param \Kalnoy\Cruddy\Contracts\Field $field
     */
    public function __construct(Entity $form, $id, Field $field)
    {
        parent::__construct($form, $id);

        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getModelValue($model)
    {
        return $this->field->getModelValueForColumn($model);
    }

    /**
     * {@inheritdoc}
     */
    public function order(QueryBuilder $builder, $data)
    {
        $this->field->order($builder, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function canOrder()
    {
        return $this->field->canOrder();
    }

    /**
     * {@inheritdoc}
     */
    public function generateLabel()
    {
        return $this->field->getLabel();
    }

    /**
     * @return \Kalnoy\Cruddy\Contracts\Field
     */
    public function getField()
    {
        return $this->form->getFields()->get($this->field);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'field' => $this->field->getId(),

        ] + parent::toArray();
    }
}