<?php namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Model as Eloquent;

interface FormInterface {

    /**
     * Get a model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    function instance();

    /**
     * Create a new model in the database.
     *
     * @param  array  $data
     *
     * @return bool
     */
    function create(array $data);

    /**
     * Update existing model.
     *
     * @param  int $id
     * @param  array  $data
     *
     * @return bool
     */
    function update(Eloquent $instance, array $data);

    /**
     * Delete a model or a set of models.
     *
     * @param  int|array $ids
     *
     * @return void
     */
    function delete($ids);

    /**
     * Get error messages.
     *
     * @return null|\Illuminate\Support\MessageBag
     */
    function errors();
}