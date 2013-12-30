<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Kalnoy\Cruddy\Entity\Fields\AbstractField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;

class Relation extends AbstractField {

    protected $entityInstance;

    public $reference;

    public $editable = true;

    public function value(Eloquent $model)
    {
        $data = $model->{$this->id};

        if ($data instanceof Collection)
        {
            return $this->convertMany($data->all());
        }

        return $data === null ? null : $this->convert($data);
    }

    protected function convert(Eloquent $model)
    {
        $id = $model->getKey();
        $title = $this->entity()->title($model);

        return compact('id', 'title');
    }

    protected function convertMany(array $data)
    {
        return array_map(array($this, 'convert'), $data);
    }

    public function process($data)
    {
        if (empty($data)) return false;

        if (isset($data['id'])) return $data['id'];

        return array_pluck($data, 'id');
    }

    public function modifyQuery(Builder $builder)
    {
        $builder->with($this->id);

        return $this;
    }

    public function entity()
    {
        if ($this->entityInstance === null)
        {
            $entity = $this->reference ? $this->reference : str_plural($this->id);

            $this->entityInstance = $this->entity->getFactory()->resolve($entity);
        }

        return $this->entityInstance;
    }

    public function query(Eloquent $model = null)
    {
        if ($model === null) $model = $this->entity->form()->instance();

        return $model->{$this->id}();
    }

    public function isMultiple()
    {
        $instance = $this->entity->form()->instance();

        return $instance->{$this->id} instanceof Collection;
    }

    public function isEditable(Eloquent $model)
    {
        return $this->editable;
    }

    public function toArray()
    {
        return parent::toArray() + array(
            'reference' => $this->entity()->getId(),
            'multiple' => $this->isMultiple(),
        );
    }

    public function getJavaScriptClass()
    {
        return "Relation";
    }

}