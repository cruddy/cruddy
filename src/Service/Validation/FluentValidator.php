<?php

namespace Kalnoy\Cruddy\Service\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Kalnoy\Cruddy\Contracts\Validator as ValidatorContract;
use Kalnoy\Cruddy\Entity;

/**
 * Fluent validator for validating input.
 *
 * This is basic implementation that uses laravel validator with few usefull features.
 *
 * @see https://github.com/lazychaser/cruddy/wiki/Validation for expanded documentation.
 *
 * @method $this    rules(array $rules)
 * @method $this    create(array $rules)
 * @method $this    update(array $rules)
 *
 * @since 1.0.0
 */
class FluentValidator extends Fluent implements ValidatorContract {

    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    /**
     * Validation errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    private $required;

    /**
     * Init validator.
     *
     * @param \Illuminate\Validation\Factory $validator
     */
    function __construct($validator = null)
    {
        $this->validator = $validator ?: \app('validator');
    }

    /**
     * {@inheritdoc}
     */
    public function validFor($action, array $input, array $labels)
    {
        $this->errors = [];

        if ($rules = $this->resolveRules($action))
        {
            $rules    = $this->processRules($rules, $input);

            $messages = $this->get('messages', []);
            $labels   = $this->get('customAttributes', []) + $labels;

            $validator = $this->validator->make($input, $rules, $messages, $labels);

            if ($validator->fails())
            {
                $this->errors = $validator->errors()->getMessages();
            }
        }

        return empty($this->errors);
    }

    /**
     * Resolve rules given rule set name.
     *
     * @param string $action
     *
     * @return array
     */
    public function resolveRules($action)
    {
        $rules = $this->get($action);
        $defaultRules = $this->get('rules');

        if ($rules === null) return $defaultRules;

        if ($defaultRules !== null) return $this->mergeRules($defaultRules, $rules);

        return $rules;
    }

    /**
     * Merge rules so that rules will be appended to the default rules.
     *
     * @param array $defaultRules
     * @param array $rules
     *
     * @return array
     */
    public function mergeRules(array $defaultRules, array $rules)
    {
        $defaultRules = $this->explodeRules($defaultRules);
        $rules = $this->explodeRules($rules);

        foreach ($rules as $attr => $rule)
        {
            if (isset($defaultRules[$attr]))
            {
                $rule = array_merge($defaultRules[$attr], $rule);
            }

            $defaultRules[$attr] = $rule;
        }

        return $defaultRules;
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    protected function explodeRules(array $rules)
    {
        foreach ($rules as &$rule)
        {
            if (is_string($rule)) $rule = explode('|', $rule);
        }

        return $rules;
    }

    /**
     * Process set of rules and replace macros with values from input array.
     *
     * {id} will be replaced with value under key of 'id' in input array.
     *
     * @param array $rules
     * @param array $input
     *
     * @return array
     */
    public function processRules(array $rules, array $input)
    {
        foreach ($rules as $key => $rule)
        {
            $rules[$key] = $this->processRule($rule, $input);
        }

        return $rules;
    }

    /**
     * Process a single rule.
     *
     * @param string $rule
     * @param array  $input
     *
     * @return mixed
     */
    public function processRule($rule, array $input)
    {
        if (is_array($rule))
        {
            foreach ($rule as &$part)
            {
                $part = $this->processRule($part, $input);
            }

            return $rule;
        }

        return preg_replace_callback('/\{([a-z_][a-z0-9_\.]*)\}/i', function ($matches) use ($input)
        {
            return \array_get($input, $matches[1], 'NULL');

        }, $rule);
    }

    /**
     * {@inheritdoc}
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * An alias for `rules`.
     *
     * @param array $rules
     *
     * @return $this
     */
    public function always(array $rules)
    {
        $this->attributes['rules'] = $rules;

        return $this;
    }

    /**
     * An alias for `create`.
     *
     * @param array $rules
     *
     * @return $this
     */
    public function fresh(array $rules)
    {
        $this->attributes[Entity::CREATE] = $rules;

        return $this;
    }

    /**
     * An alias for `update`.
     *
     * @param array $rules
     *
     * @return $this
     */
    public function existing(array $rules)
    {
        $this->attributes[Entity::UPDATE] = $rules;

        return $this;
    }

    /**
     * Get whether the required state of the field.
     *
     * @param string $fieldId
     *
     * @return bool|string
     */
    public function getRequiredState($fieldId)
    {
        $required = $this->getRequiredFields();

        return isset($required[$fieldId]) ? $required[$fieldId] : false;
    }

    /**
     * @return array
     */
    protected function getRequiredFields()
    {
        if ($this->required === null)
        {
            return $this->required = $this->collectRequiredFields();
        }

        return $this->required;
    }

    /**
     * @return array
     */
    protected function collectRequiredFields()
    {
        return $this->collectFieldsWithRule('required', $this->get('rules'), true)
            + $this->collectFieldsWithRule('required', $this->get(Entity::CREATE), Entity::CREATE)
            + $this->collectFieldsWithRule('required', $this->get(Entity::UPDATE), Entity::UPDATE);
    }

    /**
     * @param $rule
     * @param $rules
     * @param $value
     *
     * @return array
     */
    protected function collectFieldsWithRule($rule, $rules, $value)
    {
        if (empty($rules)) return [];

        $result = [];

        foreach ($rules as $attr => $ruleSet)
        {
            if ($this->hasRule($rule, $ruleSet))
            {
                $result[$attr] = $value;
            }
        }

        return $result;
    }

    /**
     * @param $rule
     * @param $ruleSet
     *
     * @return bool
     */
    protected function hasRule($rule, $ruleSet)
    {
        if ( ! is_array($ruleSet)) $ruleSet = explode('|', $ruleSet);

        return in_array($rule, $ruleSet);
    }
}