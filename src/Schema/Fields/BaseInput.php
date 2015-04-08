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
abstract class BaseInput extends BaseField {

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
            'append' => Helpers::tryTranslate($this->get('append')),
            'prepend' => Helpers::tryTranslate($this->get('prepend')),

        ] + parent::toArray();
    }

}