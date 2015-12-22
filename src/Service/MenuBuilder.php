<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Contracts\Container\Container;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Kalnoy\Cruddy\Contracts\Permissions;
use Kalnoy\Cruddy\Entity;
use Kalnoy\Cruddy\Environment;

/**
 * The menu builder class for rendering menus.
 *
 * @since 1.0.0
 */
class MenuBuilder extends \Illuminate\Html\MenuBuilder {

    /**
     * The environment.
     *
     * @var Environment
     */
    protected $env;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Initialize menu.
     *
     * @param Environment $env
     */
    public function __construct(Environment $env)
    {
        $this->env = $env;

        $this->reserved[] = 'entity';
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return array|string
     */
    protected function normalizeItem($key, $value)
    {
        if (is_string($value) and is_numeric($key) and $value !== '-')
        {
            return [ 'entity' => $value ];
        }

        return parent::normalizeItem($key, $value);
    }

    /**
     * @param array|string $options
     *
     * @return string
     */
    protected function renderItem($options)
    {
        if (is_array($options) and isset($options['entity']))
        {
            $options['data-entity'] = $options['entity'];
        }

        return parent::renderItem($options);
    }

    /**
     * @param array|string $options
     *
     * @return bool
     */
    protected function isVisible($options)
    {
        if (is_array($options) and isset($options['entity']))
        {
            $entity = $this->env->getEntities()->resolve($options['entity']);

            if ( ! $entity->isPermitted(Entity::READ)) return false;
        }

        return parent::isVisible($options);
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function getHref(array $options)
    {
        if (isset($options['entity']) and ! isset($options['url']) and ! isset($options['route']))
        {
            return $this->hrefFromRoute([ 'cruddy.index', $options['entity'] ]);
        }

        return parent::getHref($options);
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function getLabel(array $options)
    {
        $label = parent::getLabel($options);

        if ( ! $label and isset($options['entity']))
        {
            $label = $this->env->entity($options['entity'])->getPluralTitle();
        }

        return $label;
    }

    /**
     * @param $items
     *
     * @return mixed
     */
    protected function getArrayable($items)
    {
        if (is_string($items))
        {
            $callable = $this->getCallable($items);

            return $this->getArrayable(call_user_func($callable));
        }

        return parent::getArrayable($items);
    }

    /**
     * @param $items
     *
     * @return array
     */
    protected function getCallable($items)
    {
        $segments = explode('@', $items, 2);

        return [ $this->resolveClass($segments[0]), isset($segments[1]) ? $segments[1] : 'menu' ];
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    protected function resolveClass($name)
    {
        if ($this->container) return $this->container->make($name);

        return $name;
    }

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

}