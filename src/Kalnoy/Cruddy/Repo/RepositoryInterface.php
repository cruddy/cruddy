<?php

namespace Kalnoy\Cruddy\Repo;

/**
 * RepositoryInterface is responsible for fetching, searching and performing 
 * CRUD operations.
 */
interface RepositoryInterface {

    /**
     * Get new eloquent model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newModel();

   /**
    * Get an item by given id.
    *
    * @param   mixed  $id
    *
    * @return  \Illuminate\Database\Eloquent\Model
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
     * @param array                                        $options
     * @param \Kalnoy\Cruddy\Repo\SearchProcessorInterface $processor
     *
     * @return \Illuminate\Pagination\Paginator
     */
    public function search(array $options, SearchProcessorInterface $processor = null);

    /**
     * Create new eloquent model with input.
     *
     * @param array $input
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $input);

    /**
     * Update existing eloquent model with input.
     *
     * @param int    $id
     * @param array  $input
     *
     * @return \Illuminate\Database\Eloquent\Model
     * 
     * @throws \Kalnoy\Cruddy\ModelNotFoundException
     */
    public function update($id, array $input);

    /**
     * Delete a model or a set of model.
     *
     * @param int|array $ids
     *
     * @return int the number of deleted items.
     */
    public function delete($ids);

    /**
     * Get whether the field is fillable.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isFillable($key);

}