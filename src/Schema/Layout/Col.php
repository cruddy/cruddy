<?php

namespace Kalnoy\Cruddy\Schema\Layout;

class Col extends BaseFieldset {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Col';

    /**
     * Column span.
     *
     * @var int
     */
    public $span;

    /**
     * Init column.
     *
     * @param int $span
     * @param string|array|\Closure $items
     */
    public function __construct($span, $items)
    {
        parent::__construct($items);

        $this->span = $span;
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        return [ 'span' => $this->span ] + parent::compile();
    }

}