<?php

namespace Kalnoy\Cruddy\Schema\Columns;

use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema\Attribute;
use Kalnoy\Cruddy\Contracts\Column;

/**
 * Base column class.
 *
 * @method $this width(int $value)
 * @property int $width
 * 
 * @property string $orderDir
 * @method $this orderDir(string $value)
 * 
 * @since 1.0.0
 */
abstract class BaseColumn extends Attribute implements Column
{
    /**
     * Set the default order direction.
     *
     * @param string $value
     *
     * @return $this
     */
    public function orderDirection($value)
    {
        $this->set('orderDir', $value);

        return $this;
    }

    /**
     * Set descending order direction.
     *
     * @return $this
     */
    public function reversed()
    {
        $this->set('orderDir', 'desc');

        return $this;
    }

    /**
     * Get column header.
     *
     * @return string
     */
    public function getHeader()
    {
        if ($header = $this->get('header')) {
            return Helpers::tryTranslate($header);
        }

        return $this->translate('columns') ?: $this->generateLabel();
    }

    /**
     * @return string
     */
    public function getDefaultOrderDirection()
    {
        return $this->get('orderDir', 'asc');
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'width' => $this->get('width'),
            'header' => $this->getHeader(),
            'order_dir' => $this->getDefaultOrderDirection(),

        ] + parent::toArray();
    }

}