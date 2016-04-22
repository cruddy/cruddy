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
 * @property bool $hash
 * @method $this hash(bool $value = true)
 *
 * @since 1.0.0
 */
class Password extends BaseTextField
{
    /**
     * @return string
     */
    protected function getInputType()
    {
        return 'password';
    }

    /**
     * {@inheritdoc}
     */
    public function getModelValue($model)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function setModelValue($model, $value)
    {
        if (is_null($value)) {
            return $this;
        }

        return parent::setModelValue($model, $value);
    }

    /**
     * @inheritDoc
     *
     * Leaving password input without changes.
     */
    public function parseInputValue($value)
    {
        return is_null($value) || trim($value) === '' ? null : $value;
    }

    /**
     * @param array $value
     *
     * @return string
     */
    protected function processInputValue($value)
    {
        $value = parent::processInputValue($value);

        return $this->hash ? bcrypt($value) : $value;
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