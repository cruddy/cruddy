<?php namespace Kalnoy\Cruddy\Fields\Types;

use Kalnoy\Cruddy\Fields\Input;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Password extends Input {

    /**
     * The input type.
     *
     * @var string
     */
    protected $inputType = 'password';

    /**
     * Get the value of respective model's attribute.
     *
     * Password returns null.
     *
     * @param  Eloquent $model
     *
     * @return void
     */
    public function value(Eloquent $model)
    {
        return "";
    }

    public function process($value)
    {
        return empty($value) ? null : $value;
    }
}