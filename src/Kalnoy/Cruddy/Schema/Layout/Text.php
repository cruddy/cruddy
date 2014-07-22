<?php

namespace Kalnoy\Cruddy\Schema\Layout;

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

    public function __construct($contents)
    {
        $this->contents = $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        return [ 'contents' => \Kalnoy\Cruddy\try_trans($this->contents) ] + parent::compile();
    }

}