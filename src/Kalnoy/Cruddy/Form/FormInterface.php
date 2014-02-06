<?php

namespace Kalnoy\Cruddy\Form;

interface FormInterface {

    /**
     * Create a new model in the database.
     *
     * @param array $input
     * @param bool  $dryRun
     *
     * @return \Illuminate\Database\Eloquent\Model
     * 
     * @throws \Kalnoy\Cruddy\Service\Validation\ValidationException
     */
    function create(array $input, $dryRun = false);

    /**
     * Update existing model.
     *
     * @param int   $id
     * @param array $input
     * @param bool  $dryRun
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Kalnoy\Cruddy\ModelNotFoundException
     * @throws \Kalnoy\Cruddy\Service\Validation\ValidationException
     */
    function update($id, array $input, $dryRun = false);

    /**
     * Delete a model or a set of models.
     *
     * @param  int|array $ids
     *
     * @return int the number of deleted models.
     */
    function delete($ids);
}