<?php

namespace Kalnoy\Cruddy\Schema\Filters;

use Kalnoy\Cruddy\Contracts\Filter;
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
     * Get field label.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($label = $this->get('label'))
        {
            return \Kalnoy\Cruddy\try_trans($label);
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
     * @return array
     */
    public function toArray()
    {
        return [
            'label' => $this->getLabel(),

        ] + parent::toArray();
    }
}