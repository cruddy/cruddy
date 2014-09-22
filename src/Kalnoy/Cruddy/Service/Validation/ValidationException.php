<?php

namespace Kalnoy\Cruddy\Service\Validation;

use Exception;
use RuntimeException;

/**
 * Validation exception that provides validation errors.
 *
 * @since 1.0.0
 */
class ValidationException extends RuntimeException {

    /**
     * The errors.
     *
     * @var array
     */
    protected $errors;

    /**
     * Init exception.
     *
     * @param array     $errors
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     */
    function __construct(array $errors, $message = '', $code = 0, Exception $previous = null)
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