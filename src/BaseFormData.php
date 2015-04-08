<?php

namespace Kalnoy\Cruddy;

use Kalnoy\Cruddy\Service\Validation\ValidationException;

abstract class BaseFormData {

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var array
     */
    protected $input;

    /**
     * @param Entity $entity
     * @param array $data
     */
    public function __construct(Entity $entity, array $data)
    {
        $this->entity = $entity;

        $this->process($data);
    }

    /**
     * Validate the input.
     *
     * @throws ValidationException
     */
    public function validate()
    {
        if ($errors = $this->getValidationErrors())
        {
            throw new ValidationException($errors);
        }
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $result = [];
        $labels = $this->entity->getFields()->validationLabels();
        $validator = $this->entity->getValidator();

        if ( ! $validator->validFor($this->getAction(), $this->input, $labels))
        {
            $result = $validator->errors();
        }

        return $result;
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
        $this->input = $this->entity->getFields()->process($data);
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