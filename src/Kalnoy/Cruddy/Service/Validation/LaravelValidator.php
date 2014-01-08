<?php namespace Kalnoy\Cruddy\Service\Validation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;

class LaravelValidator extends Fluent implements ValidableInterface {

    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    function __construct($validator)
    {
        $this->validator = $validator;
    }

    /**
     * Perform validation with given set of rules.
     *
     * @param array  $input
     * @param string $ruleSet
     *
     * @throws ValidationException
     */
    public function validate(array $input, $ruleSet)
    {
        $rules = $this->resolveRules($ruleSet);

        if ($rules === null) return;

        $rules = $this->processRules($rules, $input);
        $messages = $this->get('messages', []);
        $attributes = $this->get('customAttributes', []);

        $validator = $this->validator->make($input, $rules, $messages, $attributes);

        if (!$validator->passes())
        {
            throw new ValidationException($validator->errors()->getMessages());
        }
    }

    /**
     * Resolve rules given rule set name.
     *
     * @param $ruleSet
     *
     * @return mixed
     */
    public function resolveRules($ruleSet)
    {
        $rules = $this->get($ruleSet);
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
        return preg_replace_callback('/{(\w+)}/', function ($matches) use ($input)
        {
            $key = $matches[1];

            return array_key_exists($key, $input) ? $input[$key] : '';

        }, $rule);
    }

    /**
     * Validate input before creating.
     *
     * @param array $input
     *
     * @return void
     * @throws ValidationException
     */
    public function beforeCreate(array $input)
    {
        $this->validate($input, 'create');
    }

    /**
     * Validate input before update.
     *
     * @param array $input
     *
     * @return mixed
     * @throws ValidationException
     */
    public function beforeUpdate(array $input)
    {
        $this->validate($input, 'update');
    }
}