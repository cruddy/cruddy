<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Contracts\ArrayableInterface;

/**
 * The schema interface.
 *
 * Schema is used by the entity to initialize components. It also provides additional
 * configuration for the UI.
 *
 * @since 1.0.0
 */
interface Schema extends ArrayableInterface {

    /**
     * Create an entity object.
     *
     * @return \Kalnoy\Cruddy\Entity
     */
    public function entity();

    /**
     * Initialize fields.
     *
     * @param \Kalnoy\Cruddy\Schema\Fields\InstanceFactory $schema
     *
     * @return void
     */
    public function fields($schema);

    /**
     * Initialize columns.
     *
     * @param \Kalnoy\Cruddy\Schema\Columns\InstanceFactory $schema
     *
     * @return void
     */
    public function columns($schema);

    /**
     * @param \Kalnoy\Cruddy\Schema\Filters\InstanceFactory $schema
     *
     * @return void
     */
    public function filters($schema);

    /**
     * Create repository.
     *
     * @return \Kalnoy\Cruddy\Contracts\Repository
     */
    public function repository();

    /**
     * Create validator.
     *
     * @return \Kalnoy\Cruddy\Contracts\Validator
     */
    public function validator();

    public function executeAction(Model $model, $action);

    /**
     * Get additional model attributes that will be available for the UI.
     *
     * @param Model $model
     * @param bool  $simplified
     *
     * @return array
     */
    public function meta(Model $model, $simplified);

}