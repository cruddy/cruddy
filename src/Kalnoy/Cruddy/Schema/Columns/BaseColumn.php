<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Schema\ColumnInterface;

/**
 * Base column class.
 *
 * @since 1.0.0 
 */
abstract class BaseColumn extends Attribute implements ColumnInterface {
    
    /**
     * The column width in pixels or percents.
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
     * Set the column width in pixels or percents.
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
     * Set the default order direction.
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
        return $this->translate('columns') ?: $this->generateLabel();
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