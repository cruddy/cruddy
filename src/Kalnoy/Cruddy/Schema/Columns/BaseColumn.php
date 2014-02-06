<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Schema\ColumnInterface;

abstract class BaseColumn extends Attribute implements ColumnInterface {
    
    /**
     * The column width.
     *
     * @var int
     */
    public $width;

    /**
     * Default order direction.
     *
     * @var string
     */
    public $orderDir = 'asc';

    /**
     * The formatter JavaScript class under `Cruddy.Formatters` namespace.
     *
     * @var string
     */
    public $formatter;

    /**
     * Optional formatter options.
     *
     * @var mixed
     */
    public $formatterOptions;

    /**
     * Set the width.
     *
     * @param int $value
     *
     * @return $this
     */
    public function width($value)
    {
        $this->width = $value;

        return $this;
    }

    /**
     * Set formatter class for the column.
     *
     * @param string $class
     * @param mixed $options
     *
     * @return $this
     */
    public function format($class, $options = null)
    {
        $this->formatter = $class;
        $this->formatterOptions = $options;

        return $this;
    }

    /**
     * Set default order direction.
     *
     * @param asc|desc $value
     *
     * @return $this
     */
    public function orderDirection($value)
    {
        $this->orderDir = $value;

        return $this;
    }

    /**
     * Get column header.
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->translate('columns') ?: \Kalnoy\Cruddy\prettify_string($this->id);
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'width' => $this->width,
            'header' => $this->getHeader(),
            'order_dir' => $this->orderDir,
            'formatter' => $this->formatter,
            'formatter_options' => $this->formatterOptions,

        ] + parent::toArray();
    }

}