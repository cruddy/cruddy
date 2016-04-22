<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

/**
 * Email input field.
 *
 * @since 1.0.0
 */
class Email extends BaseTextField
{
    /**
     * @return string
     */
    protected function getInputType()
    {
        return 'email';
    }

    /**
     * @inheritDoc
     */
    public function getRules($modelKey)
    {
        return array_merge(parent::getRules($modelKey), [ 'email' ]);
    }
}