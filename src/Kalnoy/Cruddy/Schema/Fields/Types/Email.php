<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

class Email extends BaseTextField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'email';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $inputType = 'email';
}