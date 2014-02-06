<?php

namespace Kalnoy\Cruddy\Schema;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * FieldInterface
 */
interface FieldInterface extends AttributeInterface {

    /**
     * Process input value and convert it to a valid format.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function process($value);

    /**
     * Whether to skip value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function skip($value);

}