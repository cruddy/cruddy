<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Html\HtmlBuilder;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Kalnoy\Cruddy\Contracts\Permissions;
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
     * Initialize menu.
     *
     * @param Environment $env
     */
    public function __construct(Environment $env, HtmlBuilder $html, Request $request)
    {
        parent::__construct($request);

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
     * @return bool
     */
    protected function isVisible($options)
    {
        if (is_array($options) and isset($options['entity']))
        {
            $entity = $this->env->getEntities()->resolve($options['entity']);

            if ( ! $entity->isPermitted(Permissions::VIEW)) return false;
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
}