<?php

namespace Kalnoy\Cruddy\Service\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;

class FluentValidator extends Fluent implements ValidableInterface {

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
     * Init validator.
     *
     * @param \Illuminate\Validation\Factory $validator
     */
    function __construct($validator = null)
    {
        $this->validator = $validator ?: \app('validator');
    }

    /**
     * Perform validation with given set of rules.
     *
     * @param string $action
     * @param array  $input
     *
     * @throws ValidationException
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
     * @param $action
     *
     * @return mixed
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
        foreach ($rules as $k => $rule)
        {
            if (isset($defaultRules[$k]))
            {
                $rule = $defaultRules[$k] . '|' . $rule;
            }

            $defaultRules[$k] = $rule;
        }

        return $defaultRules;
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
     * Process single rule.
     *
     * @param string $rule
     * @param array  $input
     *
     * @return mixed
     */
    public function processRule($rule, array $input)
    {
        return preg_replace_callback('/{([a-z0-9_\.]+)}/i', function ($matches) use ($input)
        {
            return \array_get($input, $matches[1], '');

        }, $rule);
    }

    /**
     * @inheritdoc
     *
     * @return array
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
        $this->attributes['create'] = $rules;

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
        $this->attributes['update'] = $rules;

        return $this;
    }
}