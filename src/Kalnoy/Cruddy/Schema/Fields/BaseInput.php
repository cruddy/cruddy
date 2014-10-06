<?php

namespace Kalnoy\Cruddy\Schema\Fields;

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
     * {@inheritdoc}
     */
    protected $canOrder = true;

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'append' => \Kalnoy\Cruddy\try_trans($this->get('append')),
            'prepend' => \Kalnoy\Cruddy\try_trans($this->get('prepend')),

        ] + parent::toArray();
    }

}