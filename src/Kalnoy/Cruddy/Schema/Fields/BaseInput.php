<?php

namespace Kalnoy\Cruddy\Schema\Fields;

abstract class BaseInput extends BaseField {

    /**
     * {@inheritdoc}
     */
    protected $canOrder = true;

    /**
     * The text that should be appended to the input.
     *
     * @var string
     */
    public $append;

    /**
     * The text that shoul be prepended to the input.
     *
     * @var string
     */
    public $prepend;

    /**
     * Set append value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function append($value)
    {
        $this->append = $value;

        return $this;
    }

    /**
     * Set prepend value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function prepend($value)
    {
        $this->prepend = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'append' => \Kalnoy\Cruddy\try_trans($this->append),
            'prepend' => \Kalnoy\Cruddy\try_trans($this->prepend),

        ] + parent::toArray();
    }

}