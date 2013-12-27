<?php namespace Kalnoy\Cruddy\Related;

use Kalnoy\Cruddy\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Builder;

abstract class AbstractRelated extends Attribute {

    protected $related;

    public function value(Eloquent $model)
    {
        return $model->{$this->id};
    }

    public function modifyQuery(Builder $builder)
    {
        $builder->with($this->id);

        return $this;
    }

    protected function resolveEntity($id)
    {
        return $this->entity->getFactory()->resolve($id);
    }

    abstract protected function resolveRelated();

    public function relation(Eloquent $model = null)
    {
        $model = $model ?: $this->entity->form()->instance();

        return $model->{$this->id}();
    }

    public function getRelated()
    {
        if ($this->related === null)
        {
            return $this->related = $this->resolveRelated();
        }

        return $this->related;
    }

    public function toArray()
    {
        return parent::toArray() + array(
            'related' => $this->getRelated()->getId(),
            'foreign_key' => $this->relation()->getPlainForeignKey(),
        );
    }

    public function getJavaScriptClass()
    {
        return "Related";
    }
}