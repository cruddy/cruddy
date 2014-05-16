<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;

/**
 * Primary field type.
 * 
 * @since 1.0.0
 */
class Primary extends String {

    /**
     * {@inheritdoc}
     */
    protected $type = 'primary';

    /**
     * {@inheritdoc}
     *
     * Primary field is hidden by default.
     */
    public $hide = true;

    /**
     * {@inheritdoc}
     * 
     * We will check for actual match rather than partial.
     */
    public function filter(Builder $builder, $data)
    {
        $builder->orWhere($this->id, '=', $data);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Unique is forced here.
     */
    public function toArray()
    {
        return ['unique' => true] + parent::toArray();
    }
}