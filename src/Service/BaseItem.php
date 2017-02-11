<?php

namespace Kalnoy\Cruddy\Service;

use Kalnoy\Cruddy\Form\Fields\BaseField;
use Kalnoy\Cruddy\Helpers;

/**
 * Class BaseItem
 *
 * @package Kalnoy\Cruddy\Service
 */
abstract class BaseItem
{
    /**
     * @var mixed
     */
    protected $owner;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    public $help;

    /**
     * BaseItem constructor.
     *
     * @param $owner
     * @param $id
     */
    public function __construct($owner, $id)
    {
        $this->id = $id;
        $this->owner = $owner;
    }

    /**
     * Set the help for this item.
     *
     * @param string $value The text or language line key
     *
     * @return $this
     */
    public function help($value)
    {
        $this->help = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'class' => $this->getUIModelClass(),
            'id' => $this->id,
            'help' => $this->getHelp(),
        ];
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        if ( ! $this->help) {
            return null;
        }
        
        return Helpers::tryTranslate($this->help);
    }

    /**
     * @return string
     */
    abstract public function getUIModelClass();
}