<?php

namespace Kalnoy\Cruddy\Schema\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Kalnoy\Cruddy\Helpers;

/**
 * Class Action
 *
 * @method $this title($value)
 * @method $this hide($value)
 * @method $this disable($value)
 *
 * @package Kalnoy\Cruddy\Schema\Actions
 */
class FluentAction extends Fluent implements Action {

    /**
     * @param Model $model
     *
     * @return string
     */
    public function getTitle(Model $model)
    {
        return $this->evaluate('title', $model) ?: Helpers::labelFromId($this->get('id'));
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function isDisabled(Model $model)
    {
        return (bool)$this->evaluate('disable', $model, false);
    }

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function isHidden(Model $model)
    {
        return (bool)$this->evaluate('hide', $model, false);
    }

    /**
     * @param string $property
     * @param Model $model
     * @param mixed $default
     *
     * @return mixed
     */
    protected function evaluate($property, Model $model, $default = null)
    {
        $value = $this->get($property, $default);

        if ($value instanceof \Closure) return $value($model);

        return $value;
    }

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function execute(Model $model)
    {
        $callback = $this->get('callback');

        if (is_string($callback))
        {
            $obj = app($callback);

            return $obj->execute($model);
        }

        if ($callback instanceof \Closure) return $callback($model);

        throw new \RuntimeException("Unknown type of callback for action.");
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->get('id');
    }

}