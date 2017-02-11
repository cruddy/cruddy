<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Columns;

use Illuminate\Database\Eloquent\Builder;
use Kalnoy\Cruddy\Entity\DataSource\DataSource;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Service\BaseItem;
use Kalnoy\Cruddy\Service\GetsValueFromModel;

/**
 * Class BaseColumn
 *
 * @package Kalnoy\Cruddy\Entity\DataSource\Columns
 */
abstract class BaseColumn extends BaseItem
{
    use GetsValueFromModel;

    /**
     * @var DataSource
     */
    protected $owner;

    /**
     * @var int
     */
    public $grow = 1;

    /**
     * @var string
     */
    public $header;

    /**
     * @var string
     */
    public $orderDirection = 'asc';

    /**
     * BaseColumn constructor.
     *
     * @param DataSource $owner
     * @param $id
     */
    public function __construct(DataSource $owner, $id)
    {
        parent::__construct($owner, $id);
    }

    /**
     * Get whether this column can order data.
     *
     * @return bool
     */
    abstract public function canOrder();

    /**
     * Get a list of relationships that needs to be loaded in order to access
     * the value on the model.
     *
     * @return array
     */
    abstract public function relationships();

    /**
     * Set reversed default order.
     *
     * @return $this
     */
    public function reversed()
    {
        $this->orderDirection = 'desc';

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function header($value)
    {
        $this->header = $value;

        return $this;
    }

    /**
     * Set the growing factor in respect to other columns.
     *
     * @param int $value
     *
     * @return $this
     */
    public function grow($value)
    {
        $this->grow = $value;

        return $this;
    }

    /**
     * @param Builder $builder
     * @param $direction
     */
    public function order(Builder $builder, $direction)
    {

    }

    /**
     * @return string
     */
    public function getHeader()
    {
        if ($this->header) {
            return Helpers::tryTranslate($this->header);
        }

        return $this->generateHeader();
    }

    /**
     * @return string
     */
    protected function generateHeader()
    {
        $header = $this->owner->getEntity()->translate("columns.{$this->id}");

        return $header ?: Helpers::labelFromId($this->id);
}

    /**
     * @return string
     */
    public function getGrow()
    {
        return $this->grow;
    }

    /**
     * @return DataSource
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @inheritDoc
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Columns.Basic';
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'grow' => $this->getGrow(),
            'header' => $this->getHeader(),
            'can_order' => $this->canOrder(),
        ] + parent::getConfig();
    }
}