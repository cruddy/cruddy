<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Kalnoy\Cruddy\Form\BaseForm;
use Kalnoy\Cruddy\Helpers;

/**
 * The field for displaying select box.
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
class Enum extends BaseInput
{
    /**
     * @var array|callable
     */
    private $items;

    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @var string
     */
    public $prompt;

    /**
     * @param BaseForm $owner
     * @param string $id
     * @param array|callable $items
     */
    public function __construct(BaseForm $owner, $id, $items)
    {
        parent::__construct($owner, $id);

        $this->items = $items;
    }

    /**
     * @return $this
     */
    public function multiple()
    {
        $this->multiple = true;
        
        return $this;
    }

    /**
     * @param string $value The text or language line key
     *
     * @return $this
     */
    public function prompt($value)
    {
        $this->prompt = $value;
        
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function processValueBeforeValidating($value)
    {
        $value = $this->parse($value);

        return $this->isMultiple() ? $value : reset($value);
    }

    /**
     * Translate items if possible.
     *
     * @param array $items
     *
     * @return array
     */
    protected function translateItems($items)
    {
        foreach ($items as $key => $value) {
            $items[$key] = Helpers::tryTranslate($value);
        }

        return $items;
    }

    /**
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @return string
     */
    public function getPrompt()
    {
        return Helpers::tryTranslate($this->prompt);
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Enum';
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'prompt' => $this->getPrompt(),
            'items' => $this->translateItems($this->getItems()),
            'multiple' => $this->isMultiple(),

        ] + parent::getConfig();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        if (is_callable($this->items)) {
            $this->items = call_user_func($this->items);
        }

        return $this->items;
    }

    /**
     * @param $value
     *
     * @return array
     */
    protected function parse($value)
    {
        if (empty($value)) return [];

        if (is_string($value)) {
            return explode(',', $value);
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        $rules = parent::getRules();
        
        if ($this->isMultiple()) {
            $rules['array'] = true;
        }
        
        $rules['in'] = array_keys($this->getItems());
        
        return array_merge(parent::getRules(), $rules);
    }

}