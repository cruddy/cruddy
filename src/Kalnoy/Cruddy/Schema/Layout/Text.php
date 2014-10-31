<?php

namespace Kalnoy\Cruddy\Schema\Layout;

use Kalnoy\Cruddy\Helpers;

class Text extends Element {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Text';

    /**
     * The contents.
     *
     * @var string
     */
    public $contents;

    /**
     * @param $contents
     */
    public function __construct($contents)
    {
        $this->contents = $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        return [ 'contents' => Helpers::tryTranslate($this->contents) ] + parent::compile();
    }

}