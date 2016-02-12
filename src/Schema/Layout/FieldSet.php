<?php

namespace Kalnoy\Cruddy\Schema\Layout;

use Kalnoy\Cruddy\Helpers;

class FieldSet extends BaseFieldSet {

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
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [ 'title' => Helpers::tryTranslate($this->title) ] + parent::toArray();
    }

}