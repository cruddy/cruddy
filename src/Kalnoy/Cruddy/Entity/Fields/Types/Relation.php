<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Entity\Columns\ColumnInterface;
use Kalnoy\Cruddy\Entity\Fields\AbstractField;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Collection;

class Relation extends AbstractField implements ColumnInterface {

    protected $entityInstance;

    public $reference;

    public $editable = true;

    public function value(Eloquent $model)
    {
        $data = $model->{$this->id};

        if ($data instanceof Collection)
        {
            return $model->exists ? $this->convertMany($data->all()) : array();
        }

        return $data === null || !$model->exists ? null : $this->convert($data);
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

    public function modifyQuery(EloquentBuilder $builder)
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

    /**
     * Get whether the column can be sorted.
     *
     * @return bool
     */
    function isSortable()
    {
        return false;
    }

    function isFilterable()
    {
        return false;
    }

    public function isSearchable()
    {
        return false;
    }

    /**
     * Apply an order to the query builder.
     *
     * @param  Builder $builder
     * @param          $direction
     *
     * @return void
     */
    function applyOrder(Builder $builder, $direction)
    {
        // TODO: Implement applyOrder() method.
    }

    /**
     * Apply constraints to the query builder.
     *
     * @param  Builder $query
     * @param  mixed   $data
     *
     * @return void
     */
    function applyConstraints(Builder $query, $data, $boolean = 'and')
    {
        // TODO: Implement applyConstraints() method.
    }
}