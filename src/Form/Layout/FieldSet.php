<?php

namespace Kalnoy\Cruddy\Form\Layout;

use Kalnoy\Cruddy\Helpers;

/**
 * Class FieldSet
 *
 * @package Kalnoy\Cruddy\Form\Layout
 */
class FieldSet extends BaseFieldSet
{
    /**
     * The title.
     *
     * @var string
     */
    public $title;

    /**
     * Init the field set.
     *
     * @param string $title
     * @param mixed $items
     */
    public function __construct($title = null, $items = null)
    {
        parent::__construct($items);

        $this->title = $title;
    }

    /**
     * @return string
     */
    public function modelClass()
    {
        return 'Cruddy.Layout.FieldSet';
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [ 'title' => Helpers::tryTranslate($this->title) ] + parent::getConfig();
    }

}