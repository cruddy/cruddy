<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\Service\Permissions\PermissionsInterface;

class MenuBuilder {

    /**
     * The environment.
     *
     * @var \Kalnoy\Cruddy\Environment
     */
    protected $env;

    /**
     * @var \Illuminate\Html\HtmlBuilder
     */
    protected $html;

    /**
     * @var \Illuminate\Routing\UrlGenerator
     */
    protected $url;

    /**
     * The permissions manager.
     *
     * @var \Kalnoy\Cruddy\Service\Permissions\PermissionsManager
     */
    protected $permissions;

    /**
     * The list of reserved attributes of the item.
     *
     * @var array
     */
    protected $reserved = [ 'entity', 'href', 'route', 'url', 'permissions', 'icon', 'secure', 'label' ];

    /**
     * Initialize menu.
     *
     * @param \Kalnoy\Cruddy\Environment $env
     */
    public function __construct(Environment $env, HtmlBuilder $html, UrlGenerator $url)
    {
        $this->env = $env;
        $this->permissions = $env->getPermissions();
        $this->html = $html;
        $this->url = $url;
    }

    /**
     * Render nav.
     *
     * @param array  $items
     * @param string $class
     *
     * @return string
     */
    public function render(array $items, $class = 'nav navbar-nav')
    {
        $html = "";

        foreach ($items as $key => $item)
        {
            $html .= $this->item($item, $key);
        }

        if (empty($html)) return "";

        return "<ul class=\"{$class}\">{$html}</ul>";
    }

    /**
     * Render item.
     *
     * @param mixed $key
     * @param mixed $item
     *
     * @return string
     */
    public function item($item, $key = null)
    {
        if ($item === '-') return '<li class="divider"></li>';

        if (is_array($item))
        {
            if (is_string($key)) return $this->dropdown($key, $item);

            return $this->custom($item);
        }

        if (is_string($key)) return $this->custom([ 'label' => $key, 'url' => $item ]);

        return $this->custom([ 'entity' => $item ]);
    }

    /**
     * Render dropdown.
     *
     * @param string $label
     * @param array  $items
     *
     * @return string
     */
    public function dropdown($label, array $items)
    {
        $inner = $this->render($items, 'dropdown-menu');

        if (empty($inner)) return '';

        $label = $this->html->entities(\Kalnoy\Cruddy\try_trans($label));

        return $this->wrap('<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$label.' <span class="caret"></span></a>'.$inner);
    }

    /**
     * Render custom item.
     *
     * @param array $options
     *
     * @return string
     */
    protected function custom(array $options)
    {
        if ( ! $this->isPermitted($options)) return '';

        $href = $this->getHref($options);
        $label = $this->getLabel($options);

        $options = array_except($options, $this->reserved);

        return $this->wrap('<a href="'.$href.'"'.$this->html->attributes($options).'>'.$label.'</a>');
    }

    /**
     * Get whether the menu item is permitted to be visible.
     *
     * @param array $options
     *
     * @return bool
     */
    protected function isPermitted(array $options)
    {
        if (isset($options['entity']))
        {
            $entity = $this->env->entity($options['entity']);

            return $this->permissions->isPermitted('view', $entity);
        }

        if (isset($options['permissions']))
        {
            $permissions = $options['permissions'];

            if ($permissions instanceof \Closure) return $permissions();

            return $this->permissions->hasAccess($permissions);
        }

        return true;
    }

    /**
     * Get link href from options.
     *
     * @param array $options
     *
     * @return string
     */
    protected function getHref(array $options)
    {
        if (isset($options['href'])) return $options['href'];

        if (isset($options['url']))
        {
            $secure = array_get($options, 'secure', false);

            return $this->hrefFromUrl($options['url'], $secure);
        }
        
        if (isset($options['route']))
        {
            return $this->hrefFromRoute($options['route']);
        }
     
        if (isset($options['entity']))
        {
            return $this->hrefFromRoute([ 'cruddy.index', $options['entity'] ]);
        }

        return $this->url->current();
    }

    /**
     * Get href from url.
     *
     * @param string|array $url
     * @param bool $secure
     *
     * @return string
     */
    protected function hrefFromUrl($url, $secure)
    {
        if ( ! is_array($url)) return $this->url->to($url, [], $secure);

        return $this->url->to($url[0], array_splice($url, 1), $secure);
    }

    /**
     * Get href from route.
     *
     * @param string|array $route
     *
     * @return string
     */
    protected function hrefFromRoute($route)
    {
        if ( ! is_array($route)) return $this->url->route($route);

        return $this->url->route($route[0], array_splice($route, 1));
    }

    /**
     * Get label from options.
     *
     * @param array $options
     *
     * @return string
     */
    protected function getLabel(array $options)
    {
        if (isset($options['label']))
        {
            $label = \Kalnoy\Cruddy\try_trans($options['label']);
        }
        else
        {
            if ( ! isset($options['entity'])) return '';

            $label = $this->env->entity($options['entity'])->getPluralTitle();
        }

        $label = $this->html->entities($label);

        if (isset($options['icon']))
        {
            $label = $this->icon($options['icon']).' '.$label;
        }

        return $label;
    }

    /**
     * Generate icon.
     *
     * @param string $icon
     *
     * @return string
     */
    protected function icon($icon)
    {
        return '<span class="glyphicon glyphicon-'.$icon.'"></span>';
    }

    /**
     * Wrap an item.
     *
     * @param string $value
     *
     * @return string
     */
    protected function wrap($value)
    {
        return "<li>{$value}</li>";
    }
}