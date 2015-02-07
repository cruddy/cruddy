<?php

namespace Kalnoy\Cruddy;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;
use Kalnoy\Cruddy\Service\Validation\ValidationException;
use Symfony\Component\Finder\Exception\OperationNotPermitedException;

class Data {

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $input;

    /**
     * @var array
     */
    protected $inner = [];

    /**
     * @var string
     */
    protected $customAction;

    /**
     * @param Entity $entity
     * @param array $data
     * @param string $id
     * @param string $cid
     */
    public function __construct(Entity $entity, array $data, $id = null, $cid = null)
    {
        $this->entity = $entity;
        $this->id = $id;
        $this->cid = $cid;

        $this->process($data);
    }

    /**
     * Validate the input;
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
        $labels = $this->entity->fields()->validationLabels();
        $validator = $this->entity->validator();

        if ( ! $validator->validFor($this->getAction(), $this->input, $labels))
        {
            $result = $validator->errors();
        }

        /** @var InnerDataCollection $item */
        foreach ($this->inner as $id => $item)
        {
            if ($errors = $item->getValidationErrors())
            {
                $result[$id] = $errors;
            }
        }

        return $result;
    }

    /**
     * Save the data and return the model.
     *
     * @return Eloquent
     */
    public function save()
    {
        if ( ! $this->isPermitted()) return false;

        $repo = $this->entity->repository();

        $model = $this->id ? $repo->find($this->id) : $repo->newModel();

        $repo->save($model, $this->getCleanedInput(), function ($model)
        {
            $this->fillModel($model);

            $this->executeCustomAction($model);

            // The event is fired when every field is filled
            $this->fireSavingEvent($model);
        });

        $this->saveInner($model);

        $this->fireSavedEvent($model);

        return $model;
    }

    /**
     * @param Model $parent
     */
    protected function saveInner(Model $parent)
    {
        /** @var InnerDataCollection $collection */
        foreach ($this->inner as $collection)
        {
            $collection->save($parent);
        }
    }

    /**
     * Set extra attributes on model.
     *
     * @param Model $model
     */
    protected function fillModel(Model $model)
    {
    }

    /**
     * Process the data.
     *
     * @param array $data
     */
    protected function process(array $data)
    {
        $this->input = $this->entity->fields()->process($data);

        $this->addInner($data);
    }

    /**
     * @param array $data
     */
    protected function addInner(array $data)
    {
        $fields = $this->entity->fields();

        foreach ($data as $id => $item)
        {
            if ($item and ($relation = $fields->get($id)) and $relation instanceof InlineRelation)
            {
                $this->inner[$relation->getId()] = InnerDataCollection::make($relation, $item);
            }
        }
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->id ? 'update' : 'create';
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    protected function getCleanedInput()
    {
        return $this->entity->fields()->cleanInput($this->getAction(), $this->input);
    }

    /**
     * @param Model $model
     */
    protected function fireSavingEvent(Model $model)
    {
        $eventResult = $this->entity->fireEvent('saving', [ $model ]);

        if ( ! is_null($eventResult))
        {
            throw new ModelNotSavedException($eventResult);
        }
    }

    /**
     * @param Model $model
     */
    public function fireSavedEvent(Model $model)
    {
        $this->entity->fireEvent('saved', [ $model ]);
    }

    /**
     * @param string $action
     */
    public function setCustomAction($action)
    {
        $this->customAction = $action;
    }

    /**
     * @return string
     */
    public function getCustomAction()
    {
        return $this->customAction;
    }

    /**
     * @param Model $model
     */
    protected function executeCustomAction(Model $model)
    {
        if ($this->customAction)
        {
            $result = $this->entity->getSchema()->executeAction($model, $this->customAction);

            if (is_string($result))
            {
                throw new ActionException($result);
            }
        }
    }

    /**
     * @return bool
     */
    protected function isPermitted()
    {
        return $this->entity->isPermitted($this->getAction());
    }
}