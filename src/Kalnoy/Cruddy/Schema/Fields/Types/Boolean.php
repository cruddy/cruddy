<?php 

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

/**
 * Boolean field.
 * 
 * @since 1.0.0
 */
class Boolean extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Boolean';

    /**
     * {@inheritdoc}
     */
    protected $type = 'bool';

    /**
     * {@inheritdoc}
     */
    protected $canOrder = true;

    /**
     * {@inheritdoc}
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        return (bool)parent::extract($model);
    }

    /**
     * {@inheritdoc}
     * 
     * @return bool
     */
    public function process($value)
    {
        return $value === 'true' || $value === '1' || $value === 'on' ? 1 : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        return trim($value) !== '';
    }

    /**
     * {@inheritdoc}
     */
    public function order(Builder $builder, $direction)
    {
        $builder->orderBy($this->id, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $builder, $data)
    {
        if ($this->keep($data))
        {
            $builder->where($this->id, '=', $this->process($data));
        }

        return $this;
    }
}