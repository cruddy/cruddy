<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

class Password extends BaseTextField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'password';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $inputType = 'password';

    /**
     * @inheritdoc
     *
     * Password returns empty string.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return string
     */
    public function extract(Eloquent $model)
    {
        return '';
    }

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return void
     */
    public function keep($value)
    {
        return ! empty(trim($value));
    }
}