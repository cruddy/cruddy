<?php

namespace Kalnoy\Cruddy\Entity\Actions;

use Kalnoy\Cruddy\Contracts\Action;
use Kalnoy\Cruddy\Helpers;

class Collection
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * Define an action.
     *
     * @param string $id
     * @param string|\Closure|null $callback
     *
     * @return FluentAction
     */
    public function define($id, $callback = null)
    {
        return $this->add(new FluentAction(compact('id', 'callback')));
    }

    /**
     * @param mixed $model
     *
     * @return array
     */
    public function export($model)
    {
        $result = [ ];

        /** @var Action $action */
        foreach ($this->items as $action) {
            if ( ! $action->isHidden($model)) {
                $result[] = $this->exportAction($action, $model);
            }
        }

        return $result;
    }

    /**
     * @param mixed $model
     * @param string $action
     *
     * @return mixed
     */
    public function execute($model, $action)
    {
        /** @var Action $actionClass */
        if ( ! $actionClass = $this->get($action)) {
            return Response::failure("The action [{$action}] is not defined.");
        }

        if ($actionClass->isDisabled($model) ||
            $actionClass->isHidden($model)
        ) {
            return Response::failure("The action [{$action}] cannot be executed.");
        }

        $result = $actionClass->execute($model);

        if (is_string($result)) {
            return Response::failure($result);
        }

        return $result instanceof Response ? $result : Response::success();
    }

    /**
     * @param Action $action
     * @param mixed $model
     *
     * @return array
     */
    protected function exportAction(Action $action, $model)
    {
        $id = $action->getId();
        $title = Helpers::tryTranslate($action->getTitle($model));
        $disabled = $action->isDisabled($model);
        $state = $action->getState($model);

        return compact('id', 'title', 'disabled', 'state');
    }

    /**
     * @param string $id
     *
     * @return Action|null
     */
    public function get($id)
    {
        return $this->has($id) ? $this->items[$id] : null;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function has($id)
    {
        $id = $id instanceof Action ? $id->getId() : $id;

        return isset($this->items[$id]);
    }

    /**
     * @param Action $action
     *
     * @return Action
     */
    public function add($action)
    {
        if (is_string($action)) {
            $action = app($action);
        }

        if ($this->has($id = $action->getId())) {
            throw new \RuntimeException("The action [{$id}] is already defined.");
        }

        return $this->items[$id] =  $action;
    }

}