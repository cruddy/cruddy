<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder;
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
     * @return string
     */
    protected function inputType()
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     */
    public function extract($model)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function keep($value)
    {
        $value = trim($value);

        return ! empty($value);
    }

    /**
     * @param Builder $builder
     * @param array $keywords
     */
    public function applyKeywordsFilter(Builder $builder, array $keywords)
    {
        // Disable search for password
    }
}