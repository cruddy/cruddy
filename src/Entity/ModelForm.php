<?php

namespace Kalnoy\Cruddy\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Kalnoy\Cruddy\ModelNotSavedException;
use Kalnoy\Cruddy\Service\Validation\ValidationException;

/**
 * This class is responsible for validation and saving specific model instance.
 *
 * @package Kalnoy\Cruddy\Entity
 */
class ModelForm
{
    /**
     * @var Form
     */
    protected $baseForm;

    /**
     * @var Model
     */
    protected $model;

    /**
     * ModelForm constructor.
     *
     * @param Form $baseForm
     * @param Model $model
     */
    public function __construct(Form $baseForm, Model $model)
    {
        $this->baseForm = $baseForm;
        $this->model = $model;
    }

    /**
     * Validate the input and save the model.
     *
     * @param array $input
     *
     * @return Form
     *
     * @throws ValidationException
     */
    public function validateAndSave(array $input)
    {
        $errors = $this->baseForm->validate($this->inputForValidation($input),
                                            $this->model->getKey());

        if ($errors) {
            throw new ValidationException($errors);
        }

        return $this->save($input);
    }

    /**
     * @param array $input
     *
     * @return $this
     */
    public function save(array $input)
    {
        $model = $this->getModel();
        $fields = $this->baseForm->getFields();
        $entity = $this->baseForm->getEntity();

        $fields->fillModel($model, $input);

        $model->getConnection()->beginTransaction();

        if (false === $entity->fireEvent('saving', [ $model ]) ||
            ! $model->save()
        ) {
            throw new ModelNotSavedException;
        }

        $fields->syncRelations($model, $input);

        $entity->fireEvent('saved', [ $model ], false);

        $model->getConnection()->commit();

        return $this;
    }

    /**
     * Extract model fields.
     *
     * @return array
     */
    public function data()
    {
        return $this->baseForm->getData($this->model);
    }

    /**
     * @return Form
     */
    public function getBaseForm()
    {
        return $this->baseForm;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param array $input
     *
     * @return array
     */
    protected function inputForValidation(array $input)
    {
        return $this->baseForm
            ->getFields()
            ->processInputForValidation($input);
    }
}