<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;
use Kalnoy\Cruddy\Entity\Columns\ColumnInterface;
use Kalnoy\Cruddy\Schema\Fields\BaseField;
use Kalnoy\Cruddy\Entity;

class Relation extends BaseField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Relation';

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    public function extract(Eloquent $model)
    {
        $data = $model->{$this->id};

        if ($data instanceof Collection)
        {
            return $model->exists ? $this->reference->simplifyAll($data) : [];
        }

        return $data === null || !$model->exists ? null : $this->reference->simplify($data);
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    public function process($data)
    {
        if (empty($data)) return '';

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
     * Get whether relation has multiple values.
     *
     * @return bool
     */
    public function isMultiple()
    {
        $instance = $this->entity->getRepository()->newModel();

        return $instance->{$this->id} instanceof Collection;
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
            'reference' => $this->reference->getId(),
            'multiple' => $this->isMultiple(),

        ] + parent::toArray();
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getFilterType()
    {
        list(, $method) = $this->getConstraintMethod();

        return $method ? self::FILTER_COMPLEX : self::FILTER_NONE;
    }

    /**
     * @inheritdoc
     *
     * @param  Builder $query
     * @param  mixed   $data
     *
     * @return $this
     */
    public function filter(Builder $query, $data)
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
     */
    protected function constraintBelongsTo(Builder $query, BelongsTo $relation, $data)
    {
        $query->where($relation->getForeignKey(), '=', $this->process($data));
    }

    /**
     * @param Builder       $query
     * @param BelongsToMany $relation
     * @param mixed         $data
     */
    protected function constraintBelongsToMany(Builder $query, BelongsToMany $relation, $data)
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

        });
    }
}