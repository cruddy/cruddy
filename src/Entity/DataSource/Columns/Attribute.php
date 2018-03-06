<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Columns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Attribute
 *
 * @package Kalnoy\Cruddy\Entity\DataSource\Columns
 */
class Attribute extends BaseColumn
{
    /**
     * @var bool
     */
    public $html = false;

    /**
     * Allow html tags inside column body.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function html($value = true)
    {
        $this->html = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function canOrder()
    {
        return ! Str::contains($this->getModelAttribute(), '.');
    }

    /**
     * @inheritdoc
     */
    public function relationships()
    {
        $attr = $this->getModelAttribute();

        if (false === $pos = strrpos($attr, '.')) {
            return [];
        }

        return [ substr($attr, 0, $pos) ];
    }

    /**
     * @inheritDoc
     */
    public function order(Builder $builder, $direction)
    {
        $builder->getQuery()->orderBy($this->getModelAttribute(),
                                      $direction ?: $this->orderDirection);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultGetter()
    {
        return [ $this, 'modelValue' ];
    }

    /**
     * @param Model $model
     * @param $attr
     *
     * @return mixed
     */
    public function modelValue(Model $model, $attr)
    {
        if ( ! $value = data_get($model, $attr)) {
            return null;
        }

        return $this->html ? $value : e($value);
    }
}