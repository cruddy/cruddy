<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;

class Slug extends BaseTextField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Slug';

    /**
     * @inheritdoc
     *
     * @var string
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
     * @inheritdoc
     * 
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
}