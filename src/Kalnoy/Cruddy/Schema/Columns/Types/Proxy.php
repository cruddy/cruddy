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
 *
 * @since 1.0.0
 */
class Proxy extends BaseColumn {

    /**
     * The field instance.
     *
     * @var FieldInterface
     */
    protected $field;

    /**
     * {@inheritdoc}
     */
    protected $class = 'Proxy';

    /**
     * {@inheritdoc}
     */
    protected $type = 'proxy';

    /**
     * Init column.
     *
     * @param Entity         $entity
     * @param string         $id
     * @param FieldInterface $field
     */
    public function __construct(Entity $entity, $id, FieldInterface $field)
    {
        parent::__construct($entity, $id);

        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        return $this->field->extractForColumn($model);
    }

    /**
     * {@inheritdoc}
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        $this->field->modifyQuery($builder);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function order(QueryBuilder $builder, $data)
    {
        $this->field->order($builder, $data);

        return $this;
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
    public function getHeader()
    {
        return $this->translate('columns') ?: $this->field->getLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'field' => $this->field->getId(),

        ] + parent::toArray();
    }
}