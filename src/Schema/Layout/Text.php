<?php

namespace Kalnoy\Cruddy\Schema\Layout;

use Kalnoy\Cruddy\Helpers;

class Text extends Element {

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
     * @return string
     */
    public function modelClass()
    {
        return 'Cruddy.Layout.Text';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [ 'contents' => Helpers::tryTranslate($this->contents) ] + parent::toArray();
    }

}