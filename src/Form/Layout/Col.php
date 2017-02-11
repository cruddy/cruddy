<?php

namespace Kalnoy\Cruddy\Form\Layout;

/**
 * Class Col
 *
 * @package Kalnoy\Cruddy\Form\Layout
 */
class Col extends BaseFieldSet
{
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
    public function __construct($items, $span = null)
    {
        parent::__construct($items);

        $this->span = $span;
    }

    /**
     * @return string
     */
    public function modelClass()
    {
        return 'Cruddy.Layout.Col';
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return [ 'span' => $this->span ] + parent::getConfig();
    }

}