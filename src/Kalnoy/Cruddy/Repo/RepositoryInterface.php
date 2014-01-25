<?php

namespace Kalnoy\Cruddy\Repo;

/**
 * RepositoryInterface is responsible for fetching, searching and performing 
 * CRUD operations.
 */
interface RepositoryInterface {

   /**
    * Get an item by given id.
    *
    * @param   mixed  $id
    *
    * @return  array
    *
    * @throws \Kalnoy\Cruddy\ModelNotFoundException
    */
    public function find($id);

    /**
     * Filter items.
     *
     * @param   string  $keyword
     * @param   array   $filter
     * @param   array   $order
     *
     * @return  \Kalnoy\Cruddy\Service\PaginatedResults
     */
    public function filter($keyword, $filter = [], $order = []);

}