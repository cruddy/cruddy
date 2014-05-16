<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

/**
 * Password field type.
 * 
 * Password field will not expose a value and will always be empty. The empty
 * password will be removed from the input.
 * 
 * @since 1.0.0
 */
class Password extends BaseTextField {

    /**
     * {@inheritdoc}
     */
    protected $type = 'password';

    /**
     * {@inheritdoc}
     */
    protected $inputType = 'password';

    /**
     * {@inheritdoc}
     */
    public function extract(Eloquent $model)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        $value = trim($value);

        return ! empty($value);
    }
}