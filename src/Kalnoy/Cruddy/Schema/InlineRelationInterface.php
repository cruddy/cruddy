<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Inline relation interface.
 * 
 * These objects are used by entity to process and save embedded entities.
 * 
 * @since 1.0.0
 */
interface InlineRelationInterface {

    /**
     * Get whether the inline relation can be processed and saved.
     *
     * @param string $action
     *
     * @return bool
     */
    public function isSaveable($action);

    /**
     * Process input and return data to save.
     *
     * @param array $input
     *
     * @return array
     */
    public function processInput($input);

    /**
     * Save previously processed data.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $data
     *
     * @return void
     */
    public function save(Eloquent $model, array $data);

    /**
     * Get the id of the relation.
     *
     * @return string
     */
    public function getId();

}