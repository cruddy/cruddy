<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Fieldset extends BaseFieldset {

    /**
     * {@inheritdoc}
     */
    protected $class = 'fieldset';

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
        return [ 'title' => \Kalnoy\Cruddy\try_trans($this->title) ] + parent::compile();
    }

}