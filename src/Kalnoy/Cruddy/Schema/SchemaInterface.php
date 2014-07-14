<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Repo\RepositoryInterface;

/**
 * The schema interface.
 * 
 * Schema is used by the entity to initialize components. It also provides additional
 * configuration for the UI.
 * 
 * @since 1.0.0
 */
interface SchemaInterface extends ArrayableInterface {

    /**
     * Create an entity object.
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function entity();

    /**
     * Initialize fields.
     *
     * @param \Kalnoy\Cruddy\Schema\InstanceFactory $schema
     *
     * @return void
     */
    public function fields($schema);

    /**
     * Initialize columns.
     *
     * @param \Kalnoy\Cruddy\Schema\InstanceFactory $schema
     *
     * @return void
     */
    public function columns($schema);

    /**
     * Create repository.
     *
     * @return \Kalnoy\Cruddy\Repo\RepositoryInterface
     */
    public function repository();

    /**
     * Create validator.
     *
     * @return \Kalnoy\Cruddy\Support\Validation\ValidableInterface
     */
    public function validator();

    /**
     * Get additional model attributes that will be available for the UI.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param bool                                $simplified
     *
     * @return array
     */
    public function extra($model, $simplified);

    /**
     * Convert model to a string.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function toString($model);

}