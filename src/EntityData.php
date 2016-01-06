<?php

namespace Kalnoy\Cruddy;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\Schema\Fields\InlineRelation;
use Symfony\Component\Finder\Exception\OperationNotPermitedException;

class EntityData extends BaseFormData
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var array
     */
    protected $inner = [ ];

    /**
     * @var Entity
     */
    protected $form;

    /**
     * @param Entity $entity
     * @param array $data
     */
    public function __construct(Entity $entity, array $data)
    {
        parent::__construct($entity, $data);
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        $errors = parent::getValidationErrors();

        /** @var InnerEntityDataCollection $item */
        foreach ($this->inner as $id => $item) {
            if ($innerErrors = $item->getValidationErrors()) {
                $errors[$id] = $innerErrors;
            }
        }

        return $errors;
    }

    /**
     * Save the data and return the model.
     *
     * @return Model
     */
    public function save()
    {
        if ( ! $this->isPermitted()) {
            throw new AccessDeniedException;
        }

        $repo = $this->form->getRepository();

        $repo->startTransaction();

        $model = $this->isExists()
            ? $repo->find($this->id)
            : $this->form->newModel();

        $repo->save($model, $this->getCleanedInput(), function ($model) {
            $this->fillModel($model);

            // The event is fired when every field is filled
            $this->fireSavingEvent($model);
        });

        $this->saveInner($model);

        $this->fireSavedEvent($model);

        $repo->commitTransaction();

        return $model;
    }

    /**
     * @param Model $parent
     */
    protected function saveInner(Model $parent)
    {
        /** @var InnerEntityDataCollection $collection */
        foreach ($this->inner as $collection) {
            try {
                $collection->save($parent);
            }

            catch (AccessDeniedException $e) {
            }
        }
    }

    /**
     * Set extra attributes on model.
     *
     * @param Model $model
     */
    protected function fillModel(Model $model) {}

    /**
     * @return bool
     */
    public function isExists()
    {
        return $this->id != null;
    }

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
        $fields = $this->form->getFields();

        foreach ($data as $id => $item) {
            if ($item && ($field = $fields->get($id)) &&
                $field instanceof InlineRelation
            ) {
                $data = InnerEntityDataCollection::make($field, $item);

                $this->inner[$field->getId()] = $data;
            }
        }
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->isExists() ? Entity::UPDATE : Entity::CREATE;
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
        return $this->form->getFields()
                          ->cleanInput($this->getAction(), $this->input);
    }

    /**
     * @param Model $model
     */
    protected function fireSavingEvent(Model $model)
    {
        $eventResult = $this->form->fireEvent('saving', [ $model ]);

        if ( ! is_null($eventResult)) {
            throw new ModelNotSavedException($eventResult);
        }
    }

    /**
     * @param Model $model
     */
    public function fireSavedEvent(Model $model)
    {
        $this->form->fireEvent('saved', [ $model ]);
    }

    /**
     * @return bool
     */
    protected function isPermitted()
    {
        return $this->form->isPermitted($this->getAction());
    }

}