<?php
namespace Kalnoy\Cruddy\Contracts;

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
     * @param mixed $model
     *
     * @return string
     */
    public function getTitle($model);

    /**
     * @param mixed $model
     *
     * @return string
     */
    public function getState($model);

    /**
     * @param mixed $model
     *
     * @return bool
     */
    public function isDisabled($model);

    /**
     * @param mixed $model
     *
     * @return bool
     */
    public function isHidden($model);

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    public function execute($model);
}