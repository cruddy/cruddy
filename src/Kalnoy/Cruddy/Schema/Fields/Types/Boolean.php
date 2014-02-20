<?php 

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

class Boolean extends BaseField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Boolean';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'bool';

    /**
     * @inheritdoc
     *
     * @var bool
     */
    protected $canOrder = true;

    /**
     * @inhertidoc
     *
     * @var string
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return bool
     */
    public function extract(Eloquent $model)
    {
        return (bool)parent::extract($model);
    }

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return int
     */
    public function process($value)
    {
        return $value === 'true' || $value === '1' || $value === 'on' ? 1 : 0;
    }

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function skip($value)
    {
        return $value === '';
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param string                             $direction
     *
     * @return $this
     */
    public function order(Builder $builder, $direction)
    {
        $builder->order($this->id, $direction);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Query\Builder $builder
     * @param mixed                              $data
     *
     * @return $this
     */
    public function filter(Builder $builder, $data)
    {
        if ( ! $this->skip($data))
        {
            $builder->where($this->id, '=', $this->process($data));
        }

        return $this;
    }
}