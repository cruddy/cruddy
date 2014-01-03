<?php namespace Kalnoy\Cruddy\Entity\Related;

use Kalnoy\Cruddy\Entity\Attribute\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractRelated extends Attribute {

    protected $related;

    /**
     * @param Eloquent $model
     * @return mixed
     */
    public function value(Eloquent $model)
    {
        return $model->{$this->id};
    }

    /**
     * @param Builder $builder
     * @return $this
     */
    public function modifyQuery(Builder $builder)
    {
        $builder->with($this->id);

        return $this;
    }

    /**
     * @param $id
     * @return \Kalnoy\Cruddy\Entity\Entity
     */
    protected function resolveEntity($id)
    {
        return $this->entity->getFactory()->resolve($id);
    }

    /**
     * Resolve related entity.
     *
     * @return \Kalnoy\Cruddy\Entity\Entity
     */
    abstract protected function resolveRelated();

    /**
     * Get foreign key that will be set on related model.
     *
     * @return mixed
     */
    abstract protected function getForeignKey();

    /**
     * Get relation query.
     *
     * @param Eloquent $model
     * @return mixed
     */
    public function relation(Eloquent $model = null)
    {
        $model = $model ?: $this->entity->form()->instance();

        return $model->{$this->id}();
    }

    /**
     * Get related entity.
     *
     * @return \Kalnoy\Cruddy\Entity\Entity
     */
    public function getRelated()
    {
        if ($this->related === null)
        {
            return $this->related = $this->resolveRelated();
        }

        return $this->related;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + array(
            'related' => $this->getRelated()->getId(),
            'foreign_key' => $this->getForeignKey(),
        );
    }
}