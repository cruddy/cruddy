<?php

namespace Kalnoy\Cruddy\Schema\Columns\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Contracts\Column;
use Kalnoy\Cruddy\Entity;

/**
 * A column for defining additional states for the row.
 */
class States extends Attribute implements Column {


    /**
     * The list of states of the row.
     *
     * @var array|\Closure
     */
    protected $states;

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Columns.Computed';
    }

    /**
     * @param Entity $entity
     * @param string $states
     */
    public function __construct(Entity $entity, $states)
    {
        parent::__construct($entity, '_states');

        $this->states = $states;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($model)
    {
        if ($this->states instanceof \Closure)
        {
            $method = $this->states;

            return (string)$method($model);
        }

        $states = [];

        foreach ($this->states as $key => $state)
        {
            if ($this->hasState($model, $state)) $states[] = $key;
        }

        return implode($states, ' ');
    }

    /**
     * Get whether model has specified state.
     *
     * @param mixed $model
     * @param string $state
     *
     * @return bool
     */
    protected function hasState($model, $state)
    {
        if ($state instanceof \Closure) return $state($model);

        $state = 'is'.camel_case($state);

        return $model->{$state}();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'hide' => true,

        ] + parent::toArray();
    }

    /**
     * @return string
     */
    public function getDefaultOrderDirection()
    {
        return null;
    }

}