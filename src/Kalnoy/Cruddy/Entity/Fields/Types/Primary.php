<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Illuminate\Database\Query\Builder;

class Primary extends Text {

    /**
     * Get whether the field is visible.
     *
     * Primary field is hidden by default.
     *
     * @var bool
     */
    public $visible = false;

    /**
     * @inheritdoc
     *
     * @param Builder $builder
     * @param mixed   $data
     * @param string  $boolean
     * @return $this
     */
    public function applyConstraints(Builder $builder, $data, $boolean = 'and')
    {
        $builder->where($this->id, '=', $data, $boolean);

        return $this;
    }
}