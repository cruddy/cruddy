<?php

namespace Kalnoy\Cruddy\Schema\Filters;

use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema\Entry;

/**
 * Class BaseFilter
 *
 * @method $this label(string $label)
 *
 * @package Kalnoy\Cruddy\Schema\Filters
 */
abstract class BaseFilter extends Entry implements Filter {

    /**
     * The list of internal keys that cannot be used as filter id.
     *
     * @var array
     */
    static $sysKeys = [ 'page', 'per_page', 'keywords', 'order_by', 'order_dir' ];

    /**
     * Get field label.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($label = $this->get('label'))
        {
            return Helpers::tryTranslate($label);
        }

        return $this->generateLabel();
    }

    /**
     * Generate a label.
     *
     * @return string
     */
    protected function generateLabel()
    {
        return $this->translate('filters') ?: parent::generateLabel();
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        if (in_array($this->id, static::$sysKeys)) return 'f_'.$this->id;

        return $this->id;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'label' => $this->getLabel(),
            'data_key' => $this->getDataKey(),

        ] + parent::toArray();
    }
}