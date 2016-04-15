<?php

namespace Kalnoy\Cruddy\Schema\Fields;

use Kalnoy\Cruddy\Helpers;

/**
 * Class BaseInput
 *
 * @method $this append(string $value)
 * @method $this prepend(string $value)
 * @property string $append
 * @property string $prepend
 *
 * @package Kalnoy\Cruddy\Schema\Fields
 */
abstract class BaseInput extends BaseField
{
    /**
     * @return bool
     */
    public function canOrder()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'append' => $this->getInputAppend(),
            'prepend' => $this->getInputPrepend(),

        ] + parent::toArray();
    }

    /**
     * @return string
     */
    public function getInputAppend()
    {
        return Helpers::tryTranslate($this->get('append'));
    }

    /**
     * @return string
     */
    public function getInputPrepend()
    {
        return Helpers::tryTranslate($this->get('prepend'));
    }

}