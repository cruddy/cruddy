<?php namespace Kalnoy\Cruddy\Entity\Columns\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Entity\Columns\AbstractColumn;
use Kalnoy\Cruddy\Entity\Columns\ColumnInterface;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use LogicException;
use RuntimeException;

/**
 * The Field column depends on an entity's field.
 * Target field must implement SortableInterface.
 */
class Field extends AbstractColumn {

    protected $fieldInstance;

    /**
     * The id of the field that is used.
     *
     * @var string
     */
    public $field;

    /**
     * Get a value of a model's attribute.
     *
     * @param  Eloquent $model
     *
     * @return mixed
     */
    public function value(Eloquent $model)
    {
        return $this->field()->value($model);
    }

    /**
     * Modify a query builder befure querying any models.
     *
     * @param  Builder $builder
     *
     * @return Field
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        $this->field()->modifyQuery($builder);

        return $this;
    }

    /**
     * Apply an order to a builder.
     *
     * @param  Builder $builder
     * @param  string  $direction
     *
     * @return Field
     */
    public function applyOrder(Builder $builder, $direction)
    {
        $this->field()->applyOrder($builder, $direction);

        return $this;
    }

    /**
     * Get a field instance.
     *
     * @throws RuntimeException
     * @throws LogicException
     * @return \Kalnoy\Cruddy\Entity\Attribute\AttributeInterface
     */
    public function field()
    {
        if ($this->fieldInstance === null)
        {
            $field = $this->field ?: $this->id;

            $this->fieldInstance = $this->entity->fields()->get($field);

            if (null === $this->fieldInstance)
            {
                throw new RuntimeException("The field {$field} is not found in {$this->entity->getId()} entity.");
            }

            if ( ! $this->fieldInstance instanceof ColumnInterface)
            {
                throw new LogicException("In order to use {$field} as a column it must implement SortableInterface.");
            }
        }

        return $this->fieldInstance;
    }

    public function applyConstraints(Builder $builder, $data, $boolean = 'and')
    {
        $this->field()->applyConstraints($builder, $data, $boolean);

        return $this;
    }

    /**
     * Get whether the query can be ordered by this column.
     *
     * @return bool
     */
    public function isSortable()
    {
        return $this->field()->isSortable();
    }

    public function isFilterable()
    {
        return $this->field()->isFilterable();
    }

    public function isSearchable()
    {
        return $this->field()->isSearchable();
    }

    public function getTitle()
    {
        $title = $this->translate("columns");

        if ($title === null)
        {
            $title = $this->field()->getLabel();
        }

        return $title ?: humanize($this->id);
    }

    /**
     * Get column configuration.
     *
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + array(
            'field' => $this->field,
        );
    }

    /**
     * Get a java script class that will serve the column.
     *
     * @return string
     */
    public function getJavaScriptClass()
    {
        return 'Field';
    }
}