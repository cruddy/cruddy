<?php

namespace Kalnoy\Cruddy;

use Kalnoy\Cruddy\Service\Validation\ValidationException;

abstract class BaseFormData
{
    /**
     * @var BaseForm
     */
    protected $form;

    /**
     * @var array
     */
    protected $input;

    /**
     * @param BaseForm $form
     * @param array $data
     */
    public function __construct(BaseForm $form, array $data)
    {
        $this->form = $form;

        $this->process($data);
    }

    /**
     * Validate the input.
     *
     * @throws ValidationException
     */
    public function validate()
    {
        if ($errors = $this->getValidationErrors()) {
            throw new ValidationException($errors);
        }
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $labels = $this->form->getFields()->validationLabels();
        $validator = $this->form->getValidator();

        if ($validator->validFor($this->getAction(), $this->input, $labels)) {
            return [];
        }

        return $validator->errors();
    }

    /**
     * @return mixed
     */
    abstract public function save();

    /**
     * Process the data.
     *
     * @param array $data
     */
    protected function process(array $data)
    {
        $this->input = $this->form->getFields()->process($data);
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return Entity::CREATE;
    }

    /**
     * @return array
     */
    public function getInput()
    {
        return $this->input;
    }
}