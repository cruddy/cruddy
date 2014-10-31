<?php

namespace Kalnoy\Cruddy\Contracts;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Contracts\Field;

/**
 * Inline relation interface.
 *
 * These objects are used by entity to process and save embedded entities.
 *
 * @since 1.0.0
 */
interface InlineRelation extends Field {

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
     * @param Eloquent $model
     * @param array    $data
     *
     * @return void
     */
    public function save(Eloquent $model, array $data);

}