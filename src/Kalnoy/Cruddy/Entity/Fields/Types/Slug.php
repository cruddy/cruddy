<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Kalnoy\Cruddy\Entity\Fields\Input;

class Slug extends Input {

    /**
     * Allowed characters. This will be a part of JavaScript regexp.
     *
     * Default value: a-z0-9\-_
     *
     * @var string
     */
    public $chars;

    /**
     * The id of reference field with which slug will be linked.
     *
     * @var string
     */
    public $ref;

    /**
     * The separator of the words. Default is dash.
     *
     * @var string
     */
    public $separator;

    /**
     * Process value.
     *
     * @param  string $value
     *
     * @return string
     */
    public function process($value)
    {
        return trim($value);
    }

    /**
     * @return array
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

    public function getJavaScriptClass()
    {
        return 'Slug';
    }
}