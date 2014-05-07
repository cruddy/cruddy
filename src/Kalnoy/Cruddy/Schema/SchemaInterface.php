<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Repo\RepositoryInterface;

/**
 * SchemaInterface
 */
interface SchemaInterface extends ArrayableInterface {

    /**
     * Create an entity object.
     *
     * @param string $id
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function entity($id);

    /**
     * Initialize fields.
     *
     * @param $schema
     *
     * @return void
     */
    public function fields($schema);

    /**
     * Initialize columns.
     *
     * @param $schema
     *
     * @return void
     */
    public function columns($schema);

    /**
     * Initialize repository.
     *
     * @return \Kalnoy\Cruddy\Repo\RepositoryInterface
     */
    public function repository();

    /**
     * Get validator.
     *
     * @return \Kalnoy\Cruddy\Support\Validation\ValidableInterface
     */
    public function validator();

    /**
     * Convert model to a string.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function toString(Eloquent $model);

}