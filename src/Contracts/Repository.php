<?php

namespace Kalnoy\Cruddy\Contracts;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Repository interface.
 *
 * The repository is responsible for fetching, searching and performing
 * CRUD operations.
 *
 * @since 1.0.0
 */
interface Repository {

   /**
    * Get an item by given id.
    *
    * @param mixed  $id
    *
    * @return Model
    *
    * @throws \Kalnoy\Cruddy\ModelNotFoundException
    */
    public function find($id);

    /**
     * Search and paginate items.
     *
     * Available options:
     *
     * - `page` -- specify the requested page
     * - `per_page` -- override the number of items per page
     *
     * @param array $options
     * @param SearchProcessor $processor
     *
     * @return array
     */
    public function search(array $options, SearchProcessor $processor = null);

    /**
     * Save the model.
     *
     * @param Model $model
     * @param array $input
     * @param callable $extra
     *
     * @return void
     */
    public function save(Model $model, array $input, Closure $extra = null);

    /**
     * Delete a model or a set of model.
     *
     * @param int|int[] $ids
     *
     * @return int the number of deleted items.
     */
    public function delete($ids);

    /**
     * @return void
     */
    public function startTransaction();

    /**
     * @return void
     */
    public function commitTransaction();

}