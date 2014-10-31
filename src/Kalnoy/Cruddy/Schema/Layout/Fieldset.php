<?php

namespace Kalnoy\Cruddy\Schema\Layout;

use Kalnoy\Cruddy\Helpers;

class Fieldset extends BaseFieldset {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Fieldset';

    /**
     * The title.
     *
     * @var string
     */
    public $title;

    /**
     * Init the fieldset.
     *
     * @param string $title
     */
    public function __construct($title = null, $items = null)
    {
        parent::__construct($items);

        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        return [ 'title' => Helpers::tryTranslate($this->title) ] + parent::compile();
    }

}