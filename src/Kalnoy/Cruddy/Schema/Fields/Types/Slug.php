<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

/**
 * Slug field type.
 * 
 * The slug uses other field's value to generate own value.
 * 
 * @since 1.0.0
 */
class Slug extends BaseTextField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Slug';

    /**
     * {@inheritdoc}
     */
    protected $type = 'slug';

    /**
     * Allowed characters. This will be a part of JavaScript regexp.
     *
     * Default value: a-z0-9\-_
     *
     * @var string
     */
    public $chars;

    /**
     * The id or array of reference field with which slug will be linked.
     *
     * @var string|array
     */
    public $ref;

    /**
     * The separator of the words. Default is dash.
     *
     * @var string
     */
    public $separator;

    /**
     * Set allowed charachters.
     *
     * @param string $value
     *
     * @return $this
     */
    public function chars($value)
    {
        $this->chars = $value;

        return $this;
    }

    /**
     * Set separator.
     *
     * @param string $value
     *
     * @return $this
     */
    public function separator($value)
    {
        $this->separator = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'chars' => $this->chars,
            'ref' => $this->ref,
            'separator' => $this->separator,

        ] + parent::toArray();
    }
}