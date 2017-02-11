<?php

namespace Kalnoy\Cruddy\Form;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Validation\Validator;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema;

/**
 * Class BaseForm
 *
 * @package Kalnoy\Cruddy\Form
 */
abstract class BaseForm
{
    /**
     * @var Layout\Layout
     */
    private $layout;

    /**
     * The field list.
     *
     * @var FieldsCollection
     */
    private $fields;

    /**
     * @var callback
     */
    protected $layoutBuilder;

    /**
     * @var callback
     */
    protected $fieldsBuilder;

    /**
     * Set the fields builder callback.
     * 
     * @param $callback
     *
     * @return $this
     */
    public function fields($callback)
    {
        $this->fieldsBuilder = $callback;
        
        return $this;
    }
    
    /**
     * Set the layout builder callback.
     * 
     * @param $callback
     *
     * @return $this
     */
    public function layout($callback)
    {
        $this->layoutBuilder = $callback;
        
        return $this;
    }

    /**
     * Extract model fields.
     *
     * @param mixed $model
     *
     * @return array
     */
    public function getData($model)
    {
        if ( ! $model) {
            return null;
        }

        return $this->getFields()->modelData($model);
    }

    /**
     * Get the attribute of the model.
     *
     * @param $model
     * @param $attribute
     *
     * @return mixed
     */
    abstract public function getModelAttributeValue($model, $attribute);

    /**
     * Set the value of attribute of the model.
     *
     * @param $model
     * @param $value
     * @param $attribute
     *
     * @return
     */
    abstract public function setModelAttributeValue($model, $value, $attribute);

    /**
     * @return FieldsCollection
     */
    protected function newFieldsCollection()
    {
        return new FieldsCollection($this, $this->getFieldsFactory());
    }

    /**
     * Translate line.
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function translate($key, $default = null)
    {
        return Helpers::translate("entities.{$key}", $default);
    }

    /**
     * Get fields collection.
     *
     * @return \Kalnoy\Cruddy\Form\FieldsCollection
     */
    public function getFields()
    {
        if (is_null($this->fields)) {
            return $this->fields = $this->buildFields();
        }
        
        return $this->fields;
    }

    /**
     * @return FieldsCollection
     */
    protected function buildFields()
    {
        $fields = $this->newFieldsCollection();
        
        if ($this->fieldsBuilder) {
            call_user_func($this->fieldsBuilder, $fields, $this);
        }
        
        return $fields;
    }

    /**
     * Get layout.
     * 
     * @return Layout\Layout
     */
    public function getLayout()
    {
        if ($this->layout === null) {
            return $this->layout = $this->buildLayout();
        }

        return $this->layout;
    }

    /**
     * @return Layout\Layout
     */
    protected function buildLayout()
    {
        $layout = new Layout\Layout;

        if ($this->layoutBuilder) {
            call_user_func($this->layoutBuilder, $layout, $this);
        }

        return $layout;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'fields' => $this->getFields()->getConfig(),
            'layout' => $this->getLayout()
                             ->isEmpty() ? null : $this->getLayout()->getConfig(),
        ];
    }

    /**
     * @return FieldsFactory
     */
    public function getFieldsFactory()
    {
        return app('cruddy.form.fields');
    }

}