<?php

namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;
use Kalnoy\Cruddy\Form\BaseForm;

/**
 * Class Form
 *
 * @package Kalnoy\Cruddy\Entity
 */
class Form extends BaseForm
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var string
     */
    protected $type;

    /**
     * Form constructor.
     *
     * @param \Kalnoy\Cruddy\Entity\Entity $entity
     * @param string $type
     */
    public function __construct(Entity $entity, $type)
    {
        $this->entity = $entity;
        $this->type = $type;
    }

    /**
     * Validate an input.
     *
     * Make sure to process the input before using
     * {@see \Kalnoy\Cruddy\Form\FieldsCollection::processInputForValidation}.
     *
     * @param array $input
     * @param mixed $modelKey
     *
     * @return array The list of errors
     */
    public function validate(array $input, $modelKey)
    {
        $validator = $this->makeValidator($input, $modelKey);

        $messages = $validator->messages()->messages();

        $innerMessages = $this->getFields()->validateInner($input);

        return array_merge_recursive($messages, $innerMessages);
    }

    /**
     * @param array $input
     * @param mixed $modelKey
     *
     * @return Validator
     */
    protected function makeValidator(array $input, $modelKey)
    {
        $labels = $this->getFields()->validationLabels();
        $rules = $this->makeRules($modelKey);

        return app(Factory::class)->make($input, $rules, [], $labels);
    }

    /**
     * @param mixed $modelKey
     *
     * @return array
     */
    public function makeRules($modelKey)
    {
        $rules = $this->fieldsRules($modelKey);

        $userRules = $this->userRules($modelKey);

        return array_merge($rules, $userRules);
    }

    /**
     * @param mixed $modelKey
     *
     * @return array
     */
    protected function fieldsRules($modelKey)
    {
        $rules = $this->getFields()->getRules();

        foreach ($rules as $attr => &$ruleSet) {
            $ruleSet = $this->fieldRulesToNative($modelKey, $attr, $ruleSet);
        }

        return $rules;
    }

    /**
     * @param mixed $modelKey
     * @param string $attr
     * @param array $rules
     *
     * @return array
     */
    protected function fieldRulesToNative($modelKey, $attr, array $rules)
    {
        $result = [];

        foreach ($rules as $rule => $params) {
            if (is_string($params)) {
                $rule = $params;
                $params = [];
            }

            $result[] = $this->fieldRuleToNative($modelKey, $attr, $rule, $params);
        }

        return $result;
    }

    /**
     * @param mixed $modelKey
     * @param string $attr
     * @param string $rule
     * @param array $params
     *
     * @return array
     */
    protected function fieldRuleToNative($modelKey, $attr, $rule, array $params)
    {
        $method = 'convert'.Str::studly($rule).'Rule';

        if (method_exists($this, $method)) {
            $params = $this->$method($modelKey, $attr, $params);
        }

        array_unshift($params, $rule);

        return $params;
    }

    /**
     * @param mixed $modelKey
     * @param string $attr
     * @param array $params
     *
     * @return array
     */
    protected function convertUniqueRule($modelKey, $attr, $params)
    {
        $extra = [];

        if ($params) {
            $extra = is_array($params[0]) ? $params[0] : $params;
        }

        $model = $this->entity->newModel();

        $rule = [
            $model->getTable(),
            $this->getFields()->get($attr)->getModelAttribute(),
            $modelKey,
            $model->getKeyName(),
        ];

        return array_merge($rule, $extra);
    }

    /**
     * @param mixed $modelKey
     *
     * @return array
     */
    protected function userRules($modelKey)
    {
        $userRules = (array)$this->getEntity()->rules($modelKey);

        $userRules = array_map(function ($rules) {
            return is_string($rules) ? explode('|', $rules) : $rules;
        }, $userRules);

        return $userRules;
    }

    /**
     * @inheritdoc
     */
    public function getData($model)
    {
        if (null === $attributes = parent::getData($model)) {
            return null;
        }

        $meta = $this->entity->modelMeta($model);

        $attributes[Entity::ID_PROPERTY] = $model->getKey();

        return compact('attributes', 'meta');
    }

    /**
     * @inheritdoc
     *
     * @param Model $model
     */
    public function getModelAttributeValue($model, $attribute)
    {
        return $model->getAttributeValue($attribute);
    }

    /**
     * @inheritdoc
     *
     * @param Model $model
     */
    public function setModelAttributeValue($model, $value, $attribute)
    {
        $model->setAttribute($attribute, $value);
    }

    /**
     * @inheritdoc
     */
    public function translate($key, $default = null)
    {
        return $this->entity->translate($key, $default);
    }

    /**
     * @return FieldsCollection
     */
    public function getFields()
    {
        return parent::getFields();
    }
    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    public function getFieldsFactory()
    {
        return app('cruddy.entity.fields');
    }

    /**
     * @inheritdoc
     */
    protected function newFieldsCollection()
    {
        return new FieldsCollection($this, $this->getFieldsFactory());
    }

    /**
     * Get whether the form is for updating model.
     * 
     * @return bool
     */
    public function updates()
    {
        return $this->type == Entity::UPDATE;
    }

    /**
     * Get whether this form is for creating model.
     * 
     * @return bool
     */
    public function creates()
    {
        return $this->type == Entity::CREATE;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
}