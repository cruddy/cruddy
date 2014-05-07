<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * InlineRelationInterface
 */
interface InlineRelationInterface {

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