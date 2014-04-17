<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;

class Primary extends String {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'primary';

    /**
     * @inheritdoc
     *
     * Primary field is hidden by default.
     *
     * @var bool
     */
    public $hide = true;

    /**
     * @inheritdoc
     *
     * @param Illuminate\Database\Query\Builder $builder
     * @param mixed                             $data
     * 
     * @return $this
     */
    public function applyConstraints(Builder $builder, $data)
    {
        $builder->orWhere($this->id, '=', $data, $boolean);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * Unique is forced here.
     *
     * @return array
     */
    public function toArray()
    {
        return ['unique' => true] + parent::toArray();
    }
}