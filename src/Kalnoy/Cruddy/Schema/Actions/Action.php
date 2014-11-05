<?php
namespace Kalnoy\Cruddy\Schema\Actions;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface Action
 *
 * @package Kalnoy\Cruddy\Schema\Actions
 */
interface Action {

    /**
     * @return string
     */
    public function getId();

    /**
     * @param Model $model
     *
     * @return string
     */
    public function getTitle(Model $model);

    /**
     * @param Model $model
     *
     * @return string
     */
    public function getState(Model $model);

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function isDisabled(Model $model);

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function isHidden(Model $model);

    /**
     * @param Model $model
     *
     * @return mixed
     */
    public function execute(Model $model);
}