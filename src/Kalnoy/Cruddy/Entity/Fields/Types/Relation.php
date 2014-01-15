<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Kalnoy\Cruddy\Entity\Columns\ColumnInterface;
use Kalnoy\Cruddy\Entity\Fields\AbstractField;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;

class Relation extends AbstractField implements ColumnInterface {

    /**
     * @var \Kalnoy\Cruddy\Entity\Entity
     */
    protected $entityInstance;

    /**
     * The id of the entity that this relation refers to.
     *
     * @var string
     */
    public $reference;

    /**
     * @var bool
     */
    public $editable = true;

    /**
     * @param Eloquent $model
     *
     * @return mixed
     */
    public function value(Eloquent $model)
    {
        $data = $model->{$this->id};

        if ($data instanceof Collection)
        {
            return $model->exists ? $this->convertMany($data->all()) : array();
        }

        return $data === null || !$model->exists ? null : $this->convert($data);
    }

    /**
     * Convert a model to an array with id and title.
     *
     * @param Eloquent $model
     *
     * @return array
     */
    protected function convert(Eloquent $model)
    {
        $id = $model->getKey();
        $title = $this->entity()->title($model);

        return compact('id', 'title');
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function convertMany(array $data)
    {
        return array_map(array($this, 'convert'), $data);
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    public function process($data)
    {
        if (empty($data)) return false;

        if (isset($data['id'])) return $data['id'];

        return array_pluck($data, 'id');
    }

    /**
     * @inheritdoc
     *
     * @param EloquentBuilder $builder
     *
     * @return $this
     */
    public function modifyQuery(EloquentBuilder $builder)
    {
        $builder->with($this->getRelationId());

        return $this;
    }

    /**
     * Get referenced entity instance.
     *
     * @return \Kalnoy\Cruddy\Entity\Entity
     */
    public function entity()
    {
        if ($this->entityInstance === null)
        {
            $entity = $this->reference ? $this->reference : str_plural($this->id);

            $this->entityInstance = $this->entity->getFactory()->resolve($entity);
        }

        return $this->entityInstance;
    }

    /**
     * Get relation query.
     *
     * @param Eloquent $model
     *
     * @return mixed
     */
    public function query(Eloquent $model = null)
    {
        if ($model === null) $model = $this->entity->form()->instance();

        return $model->{$this->getRelationId()}();
    }

    /**
     * Get whether relation has multiple values.
     *
     * @return bool
     */
    public function isMultiple()
    {
        $instance = $this->entity->form()->instance();

        return $instance->{$this->id} instanceof Collection;
    }

    /**
     * @inheritdoc
     *
     * @param Eloquent $model
     *
     * @return bool
     */
    public function isEditable(Eloquent $model)
    {
        return $this->editable;
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
            'reference' => $this->entity()->getId(),
            'multiple' => $this->isMultiple(),

        ] + parent::toArray();
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getJavaScriptClass()
    {
        return "Relation";
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    function isSortable()
    {
        return false;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    function isFilterable()
    {
        list(, $method) = $this->getConstraintMethod();

        return (bool)$method;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }

    /**
     * Get the id of the relation.
     *
     * @return string
     */
    public function getRelationId()
    {
        return Str::camel($this->id);
    }

    /**
     * @inheritdoc
     *
     * @param  Builder $builder
     * @param          $direction
     *
     * @return $this
     */
    public function applyOrder(Builder $builder, $direction)
    {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param  Builder $query
     * @param  mixed   $data
     *
     * @return $this
     */
    public function applyConstraints(Builder $query, $data, $boolean = 'and')
    {
        list($relation, $method) = $this->getConstraintMethod();

        if ($method)
        {
            $this->$method($query, $relation, $data, $boolean);
        }

        return $this;
    }

    /**
     * Resolve relation and constraint method.
     *
     * @return array
     */
    protected function getConstraintMethod()
    {
        $relation = $this->query();

        $method = 'constraint'.class_basename($relation);

        return method_exists($this, $method) ? [ $relation, $method ] : [ $relation, null ];
    }

    /**
     * @param Builder   $query
     * @param BelongsTo $relation
     * @param int       $data
     * @param bool      $boolean
     */
    protected function constraintBelongsTo(Builder $query, BelongsTo $relation, $data, $boolean)
    {
        $query->where($relation->getForeignKey(), '=', $this->process($data), $boolean);
    }

    /**
     * @param Builder       $query
     * @param BelongsToMany $relation
     * @param mixed         $data
     * @param bool          $boolean
     */
    protected function constraintBelongsToMany(Builder $query, BelongsToMany $relation, $data, $boolean)
    {
        $data = $this->process($data);

        $query->whereExists(function (Builder $q) use ($relation, $data)
        {
            $connection = $q->getConnection();
            $keyName = $connection->raw($relation->getParent()->getQualifiedKeyName());

            $q
                ->from($relation->getTable())
                ->select($connection->raw('1'))
                ->where($relation->getForeignKey(), $keyName)
                ->where($relation->getOtherKey(), $data);

        }, $boolean);
    }
}