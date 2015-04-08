<?php

namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;
use Symfony\Component\Finder\Exception\OperationNotPermitedException;

class EntityData extends BaseFormData {

    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $inner = [];

    /**
     * @var string
     */
    protected $customAction;

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $result = parent::getValidationErrors();

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
     * @return Model
     */
    public function save()
    {
        if ( ! $this->isPermitted()) throw new AccessDeniedException;

        $repo = $this->entity->getRepository();

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
            try
            {
                $collection->save($parent);
            }

            catch (AccessDeniedException $e) {}
        }
    }

    /**
     * Set extra attributes on model.
     *
     * @param Model $model
     */
    protected function fillModel(Model $model) {}

    /**
     * Process the data.
     *
     * @param array $data
     */
    protected function process(array $data)
    {
        parent::process($data);

        $this->addInner($data);
    }

    /**
     * @param array $data
     */
    protected function addInner(array $data)
    {
        $fields = $this->entity->getFields();

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
        return $this->id ? Entity::UPDATE : Entity::CREATE;
    }

    /**
     * @param mixed $value
     */
    public function setId($value)
    {
        $this->id = $value;
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
        return $this->entity->getFields()->cleanInput($this->getAction(), $this->input);
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
            $this->entity->getActions()->execute($model, $this->customAction);
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