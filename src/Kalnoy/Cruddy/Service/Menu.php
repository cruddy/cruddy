<?php

namespace Kalnoy\Cruddy\Service;

use Kalnoy\Cruddy\Environment;
use Kalnoy\Cruddy\Service\Permissions\PermissionsInterface;

class Menu {

    /**
     * The environment.
     *
     * @var \Kalnoy\Cruddy\Environment
     */
    protected $env;

    /**
     * The permissions manager.
     *
     * @var \Kalnoy\Cruddy\Service\Permissions\PermissionsManager
     */
    protected $permissions;

    /**
     * Initialize menu.
     *
     * @param \Kalnoy\Cruddy\Environment $env
     */
    public function __construct(Environment $env)
    {
        $this->env = $env;
        $this->permissions = $env->getPermissions();
    }

    /**
     * Render items.
     *
     * @param array $items
     *
     * @return string
     */
    public function render(array $items)
    {
        return $this->menu($items, "nav navbar-nav");
    }

    /**
     * Render nav.
     *
     * @param array  $items
     * @param string $class
     *
     * @return string
     */
    protected function menu(array $items, $class)
    {
        $html = "";

        foreach ($items as $key => $item)
        {
            $html .= $this->item($key, $item);
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
    protected function item($key, $item)
    {
        if (is_array($item))
        {
            if (is_numeric($key)) return $this->custom($item);

            return $this->dropdown($key, $item);
        }

        if (is_numeric($key)) return $this->entity($item);

        return $this->custom(
        [
            'label' => $key,
            'url' => $item,
        ]);
    }

    /**
     * Render dropdown.
     *
     * @param string $label
     * @param array  $items
     *
     * @return string
     */
    protected function dropdown($label, array $items)
    {
        $inner = $this->menu($items, 'dropdown-menu');

        if (empty($inner)) return '';

        $label = \Kalnoy\Cruddy\try_trans($label);

        return $this->wrap('<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.e($label).' <span class="caret"></span></a>'.$inner);
    }

    /**
     * Render custom item.
     *
     * @param array $data
     *
     * @return string
     */
    protected function custom(array $data)
    {
        if (isset($data['permissions']) && !$this->permissions->hasAccess($data['permissions']))
        {
            return '';
        }

        $class = isset($data['class']) ? ' class="'.$data['class'].'"' : "";

        $data['label'] = \Kalnoy\Cruddy\try_trans($data['label']);

        return $this->wrap('<a href="'.$data['url'].'"'.$class.'>'.e($data['label']).'</a>');
    }

    /**
     * Render entity.
     *
     * @param string $id
     *
     * @return string
     */
    protected function entity($id)
    {
        $entity = $this->env->entity($id);

        if (!$this->permissions->canView($entity)) return "";

        return $this->custom(
        [
            'label' => $entity->getPluralTitle(),
            'url' => route('cruddy.index', [ $id ]),
            'class' => 'entity',
        ]);
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