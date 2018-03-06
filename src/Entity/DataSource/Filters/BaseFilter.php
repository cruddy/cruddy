<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Filters;

use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Entity\DataSource\DataSource;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema\Entry;
use Kalnoy\Cruddy\Service\BaseItem;

/**
 * Class BaseFilter
 *
 * @method $this label(string $label)
 *
 * @package Kalnoy\Cruddy\Schema\Filters
 */
abstract class BaseFilter extends BaseItem
{
    /**
     * The list of internal keys that cannot be used as filter id.
     *
     * @var array
     */
    static $sysKeys = [ 'page', 'per_page', 'keywords', 'order_by', 'order_dir' ];

    /**
     * @var DataSource
     */
    protected $owner;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * BaseFilter constructor.
     *
     * @param DataSource $owner
     * @param $id
     */
    public function __construct(DataSource $owner, $id)
    {
        parent::__construct($owner, $id);
    }

    /**
     * @param $query
     * @param $value
     */
    abstract public function apply($query, $value);

    /**
     * @param $value
     *
     * @return $this
     */
    public function callback($value)
    {
        $this->callback = $value;

        return $this;
    }

    /**
     * Get field label.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->label) {
            return Helpers::tryTranslate($this->label);
        }

        return $this->generateLabel();
    }

    /**
     * @return string
     */
    public function generateLabel()
    {
        if ($label = $this->owner->getEntity()->translate("filters.{$this->id}")) {
            return $label;
        }

        if ($label = $this->owner->getEntity()->translate("fields.{$this->id}")) {
            return $label;
        }

        return Helpers::labelFromId($this->id);
    }

    /**
     * @return string
     */
    public function getDataKey()
    {
        if (in_array($this->id, static::$sysKeys)) {
            return 'f_'.$this->id;
        }

        return $this->id;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'label' => $this->getLabel(),
            'data_key' => $this->getDataKey(),

        ] + parent::getConfig();
    }
}