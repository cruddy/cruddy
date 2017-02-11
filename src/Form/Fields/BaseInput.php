<?php

namespace Kalnoy\Cruddy\Form\Fields;

use Kalnoy\Cruddy\Helpers;

/**
 * This is a base class for fields that are going to display `<input>` element
 * like email, password, string, number, etc.
 * 
 * @method $this min(int $value)
 * @method $this max(int $value)
 * @method $this between(int $min, int $max)
 *
 * @package Kalnoy\Cruddy\Form\Fields
 */
abstract class BaseInput extends BaseField
{
    /**
     * @var string
     */
    public $append;

    /**
     * @var string
     */
    public $prepend;

    /**
     * @var string
     */
    public $placeholder;

    /**
     * Set the text that will be displayed after the input.
     * 
     * @param string $text The text or language line key
     *
     * @return $this
     */
    public function append($text)
    {
        $this->append = $text;
        
        return $this;
    }

    /**
     * Set the text that will be displayed before the input.
     * 
     * @param string $text The text or language line key
     *
     * @return $this
     */
    public function prepend($text)
    {
        $this->prepend = $text;
        
        return $this;
    }

    /**
     * @param string $value The text or language line key
     *
     * @return $this
     */
    public function placeholder($value)
    {
        $this->placeholder = Helpers::processString($value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function processValueBeforeSetting($value)
    {
        return Helpers::processString($value);
    }

    /**
     * @return string
     */
    public function getInputAppend()
    {
        return $this->append ? Helpers::tryTranslate($this->append) : null;
    }

    /**
     * @return string
     */
    public function getInputPrepend()
    {
        return $this->prepend ? Helpers::tryTranslate($this->prepend) : null;
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Fields.Input';
    }

    /**
     * Get the type of the <input> tag.
     *
     * @return string
     */
    public function getInputType()
    {
        return 'text';
    }

    /**
     * @return null|string
     */
    public function getPlaceholder()
    {
        return $this->placeholder
            ? Helpers::tryTranslate($this->placeholder)
            : null;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'input_type' => $this->getInputType(),
            'append' => $this->getInputAppend(),
            'prepend' => $this->getInputPrepend(),
            'placeholder' => $this->getPlaceholder(),

        ] + parent::getConfig();
    }

}