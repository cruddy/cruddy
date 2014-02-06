<?php

namespace Kalnoy\Cruddy\Service\Validation;

interface ValidableInterface {

    /**
     * Check whether an input is valid for new item.    
     *
     * @param array $input
     *
     * @return void
     */
    public function validForCreation(array $input);

    /**
     * Check whether an input is valid for update.  
     *
     * @param array $input
     *
     * @return mixed
     */
    public function validForUpdate(array $input);

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function errors();
}