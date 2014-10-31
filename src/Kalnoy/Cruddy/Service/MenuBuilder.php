<?php

namespace Kalnoy\Cruddy\Service;

use Illuminate\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;
use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\Lang;

/**
 * The menu builder class for rendering menus.
 *
 * @since 1.0.0
 */
class MenuBuilder {

    /**
     * The environment.
     *
     * @var Environment
     */
    protected $env;

    /**
     * @var HtmlBuilder
     */
    protected $html;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * The permissions manager.
     *
     * @var \Kalnoy\Cruddy\Service\Permissions\PermissionsManager
     */
    protected $permissions;

    /**
     * @var Lang
     */
    protected $lang;

    /**
     * The list of reserved attributes of the item.
     *
     * @var array
     */
    protected $reserved = [ 'entity', 'href', 'route', 'url', 'permissions', 'icon', 'secure', 'label', 'items' ];

    /**
     * Initialize menu.
     *
     * @param Environment $env
     */
    public function __construct(Environment $env, Lang $lang, HtmlBuilder $html, UrlGenerator $url)
    {
        $this->env = $env;
        $this->permissions = $env->getPermissions();
        $this->lang = $lang;
        $this->html = $html;
        $this->url = $url;
    }

    /**
     * Render nav.
     *
     * @param array        $items
     * @param array|string $options
     *
     * @return string
     */
    public function render(array $items, $options = 'nav navbar-nav')
    {
        $items = $this->normalizeItems($items);

        if (empty($items)) return '';

        return $this->renderMenu($items, is_array($options) ? $options : [ 'class' => $options ]);
    }

    /**
     * Render a menu with normalized items.
     *
     * @param array  $items
     * @param array $options
     *
     * @return string
     */
    protected function renderMenu(array $items, array $options)
    {
        $html = array_reduce($items, function ($carry, $item)
        {
            return $carry.PHP_EOL.$this->renderItem($item);

        }, '');

        $options = $this->html->attributes($options);

        return "<ul{$options}>{$html}</ul>";
    }

    /**
     * Normalize items.
     *
     * @param array $items
     *
     * @return array
     */
    protected function normalizeItems(array $items)
    {
        $data = [];

        foreach ($items as $key => $value)
        {
            if ($item = $this->normalizeItem($key, $value)) $data[] = $item;
        }

        return $this->cleanItems($data);
    }

    /**
     * Normalize an item to be consumable by `item` method.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return array|string
     */
    protected function normalizeItem($key, $value)
    {
        if ($value === '-') return '-';

        if (is_array($value))
        {
            if (is_string($key))
            {
                $value = [ 'label' => $key, 'items' => $value ];
            }

            if (isset($value['items']))
            {
                $value['items'] = $this->normalizeItems($value['items']);

                if (empty($value['items'])) return null;
            }

            return $value;
        }

        if (is_string($key)) return [ 'label' => $key, 'url' => $value ];

        return [ 'entity' => $value ];
    }

    /**
     * Remove non-permitted items and repeated dividers.
     *
     * @param array $items
     *
     * @return array
     */
    protected function cleanItems(array $items)
    {
        $items = array_values(array_filter($items, [ $this, 'isPermitted' ]));

        $data = [];
        $i = 0;
        $total = count($items);

        while ($i < $total)
        {
            if ($items[$i] === '-')
            {
                if ( ! empty($data)) $data[] = '-';

                // Skip repeated dividers
                while (++$i < $total and $items[$i] === '-');
            }
            else
            {
                $data[] = $items[$i++];
            }
        }

        // Remove last divider
        if (end($data) === '-') array_pop($data);

        return $data;
    }

    /**
     * Render item.
     *
     * @param array $data
     *
     * @return string
     */
    public function item(array $data)
    {
        if ( ! $this->isPermitted($data)) return '';

        return $this->renderItem($data);
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
        $items = $this->normalizeItems($items);

        if (empty($items)) return '';

        return $this->renderItem([ 'label' => $label, 'items' => $items]);
    }

    /**
     * Render a normalized dropdown.
     *
     * @param string $label
     * @param array  $items
     *
     * @return string
     */
    protected function renderDropdown($label, array $items)
    {
        $inner = $this->renderMenu($items, 'dropdown-menu');

        return $this->wrap('<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$label.' <span class="caret"></span></a>'.$inner);
    }

    /**
     * Render an item.
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function renderItem($data)
    {
        if ($data === '-') return '<li class="divider"></li>';

        $label = $this->getLabel($data);
        $href = $this->getHref($data);
        $inner = '';

        if (isset($data['items']))
        {
            $inner = $this->renderMenu($data['items'], [ 'class' => 'dropdown-menu' ]);

            $data['class'] = isset($data['class']) ? 'dropdown-toggle '.$data['class'] : 'dropdown-toggle';
            $data['data-toggle'] = 'dropdown';
        }

        $data = array_except($data, $this->reserved);

        return $this->wrap('<a href="'.$href.'"'.$this->html->attributes($data).'>'.$label.'</a>'.$inner);
    }

    /**
     * Get a caret element.
     *
     * @return string
     */
    public function caret()
    {
        return '<span class="caret"></span>';
    }

    /**
     * Get whether the menu item is permitted to be visible.
     *
     * @param array|string $options
     *
     * @return bool
     */
    protected function isPermitted($options)
    {
        if ( ! is_array($options)) return true;

        if (isset($options['entity']))
        {
            $entity = $this->env->entity($options['entity']);

            return $this->permissions->isPermitted('view', $entity);
        }

        if (isset($options['permissions']))
        {
            return value($options['permissions']);
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
        if (isset($options['items'])) return '#';

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
     * Get a href from route.
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
     * Get a label from options.
     *
     * @param array $options
     *
     * @return string
     */
    protected function getLabel(array $options)
    {
        $label = '';

        if (isset($options['label']))
        {
            $label = $this->lang->tryTranslate($options['label']);
            $label = $this->html->entities($label);
        }
        else if (isset($options['entity']))
        {
            $label = $this->env->entity($options['entity'])->getPluralTitle();
        }

        if (isset($options['icon']))
        {
            $label = $this->icon($options['icon']).($label ? ' '.$label : '');
        }

        if (isset($options['items']))
        {
            $label .= ' '.$this->caret();
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