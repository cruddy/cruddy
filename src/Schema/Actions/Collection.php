<?php

namespace Kalnoy\Cruddy\Schema\Actions;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Cruddy\ActionException;
use Kalnoy\Cruddy\Helpers;

class Collection extends \Illuminate\Support\Collection {

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
     * @param Model $model
     *
     * @return array
     */
    public function export(Model $model)
    {
        $result = [];

        /** @var FluentAction $action */
        foreach ($this->items as $action)
        {
            if ( ! $action->isHidden($model)) $result[] = $this->exportAction($action, $model);
        }

        return $result;
    }

    /**
     * @param Model $model
     * @param string $action
     *
     * @return mixed
     */
    public function execute(Model $model, $action)
    {
        /** @var FluentAction $action */
        if ( ! $action = $this->get($action))
        {
            throw new \RuntimeException("The action [{$action}] is not defined.");
        }

        if ($action->isDisabled($model) or $action->isHidden($model))
        {
            throw new \RuntimeException("The action [{$action}] cannot be executed.");
        }

        $result = $action->execute($model);

        if (is_string($result))
        {
            throw new ActionException($result);
        }

        return $result;
    }

    /**
     * @param Action $action
     * @param Model $model
     *
     * @return array
     */
    protected function exportAction(Action $action, Model $model)
    {
        $id = $action->getId();
        $title = Helpers::tryTranslate($action->getTitle($model));
        $disabled = $action->isDisabled($model);
        $state = $action->getState($model);

        return compact('id', 'title', 'disabled', 'state');
    }

    /**
     * @param Action $action
     *
     * @return Action
     */
    public function add($action)
    {
        if (is_string($action))
        {
            $action = app($action);
        }

        if ($this->has($id = $action->getId()))
        {
            throw new \RuntimeException("The action [{$id}] already defined.");
        }

        $this->put($id, $action);

        return $action;
    }

}