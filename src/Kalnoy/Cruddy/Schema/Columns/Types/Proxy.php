<?php

namespace Kalnoy\Cruddy\Schema\Columns\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Kalnoy\Cruddy\Schema\Columns\BaseColumn;
use Kalnoy\Cruddy\Schema\FieldInterface;
use Kalnoy\Cruddy\Entity;

/**
 * Proxy relies on a field to do stuff. It just passes calls to the field.
 */
class Proxy extends BaseColumn {

    /**
     * The field instance.
     *
     * @var \Kalnoy\Cruddy\Schema\FieldInterface
     */
    protected $field;

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Proxy';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'proxy';

    /**
     * Init column.
     *
     * @param \Kalnoy\Cruddy\Entity                $entity
     * @param string                               $id
     * @param \Kalnoy\Cruddy\Schema\FieldInterface $field
     */
    public function __construct(Entity $entity, $id, FieldInterface $field)
    {
        parent::__construct($entity, $id);

        $this->field = $field;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Eloquent $model)
    {
        return $this->field->extract($model);
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return $this
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        $this->field->modifyQuery($builder);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param mixed                              $data
     *
     * @return $this
     */
    public function order(QueryBuilder $builder, $data)
    {
        $this->field->order($builder, $data);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param mixed                              $data
     *
     * @return $this
     */
    public function filter(QueryBuilder $builder, $data)
    {
        $this->field->filter($builder, $data);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function canOrder()
    {
        return $this->field->canOrder();
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getFilterType()
    {
        return $this->field->getFilterType();
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'field' => $this->field->getId(),

        ] + parent::toArray();
    }
}