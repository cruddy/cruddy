<?php namespace Kalnoy\Cruddy\Service\Validation;

interface ValidableInterface {

    /**
     * Validate input before creating.
     *
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     */
    public function beforeCreate(array $input);

    /**
     * Validate input before update.
     *
     * @param array $input
     *
     * @return mixed
     * @throws ValidationException
     */
    public function beforeUpdate(array $input);
}