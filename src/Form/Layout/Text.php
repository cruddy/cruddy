<?php

namespace Kalnoy\Cruddy\Form\Layout;

use Kalnoy\Cruddy\Helpers;

/**
 * Class Text
 *
 * @package Kalnoy\Cruddy\Form\Layout
 */
class Text extends Element
{
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
     * @inheritdoc
     */
    public function getConfig()
    {
        return $this->getContents();
    }

    /**
     * @return array
     */
    public function getContents()
    {
        return [ 'contents' => Helpers::tryTranslate($this->contents) ] + parent::getConfig();
    }

}