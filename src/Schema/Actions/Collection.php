<?php

namespace Kalnoy\Cruddy\Schema\Actions;

use Kalnoy\Cruddy\ActionException;
use Kalnoy\Cruddy\Contracts\Action;
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
     * @param mixed $model
     *
     * @return array
     */
    public function export($model)
    {
        $result = [];

        /** @var Action $action */
        foreach ($this->items as $action)
        {
            if ( ! $action->isHidden($model)) $result[] = $this->exportAction($action, $model);
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
        /** @var Action $action */
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
            throw new \RuntimeException("The action [{$id}] is already defined.");
        }

        $this->put($id, $action);

        return $action;
    }

}