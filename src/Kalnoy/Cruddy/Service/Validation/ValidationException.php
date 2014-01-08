<?php namespace Kalnoy\Cruddy\Service\Validation;

use Exception;
use RuntimeException;

class ValidationException extends RuntimeException {

    protected $errors;

    function __construct(array $errors, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}