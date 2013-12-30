<?php namespace Kalnoy\Cruddy;

use Kalnoy\Cruddy\Entity\Factory as EntityFactory;

class Menu {

    protected $entities;

    protected $permissions;

    public function __construct(EntityFactory $entities, PermissionsInterface $permissions)
    {
        $this->entities = $entities;
        $this->permissions = $permissions;
    }

    public function render(array $items)
    {
        return $this->menu($items, "nav navbar-nav");
    }

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

    protected function item($key, $item)
    {
        if (is_array($item))
        {
            if (is_numeric($key)) return $this->custom($item);

            return $this->dropdown($key, $item);
        }

        if ($item[0] === "@") return $this->entity(substr($item, 1));

        return $this->custom(array(
            'label' => $key,
            'url' => $item,
        ));
    }

    protected function dropdown($label, array $items)
    {
        $inner = $this->menu($items, "dropdown-menu");

        if (empty($inner)) return "";

        $label = try_trans($label);

        return $this->wrap('<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.e($label).' <span class="caret"></span></a>'.$inner);
    }

    protected function custom(array $data)
    {
        if (isset($data["permit"]) && !$this->permissions->hasAccess($data["permit"]))
        {
            return "";
        }

        $class = isset($data["class"]) ? ' class="'.$data["class"].'"' : "";

        $data['label'] = try_trans($data['label']);

        return $this->wrap('<a href="'.$data['url'].'"'.$class.'>'.e($data['label']).'</a>');
    }

    protected function entity($id)
    {
        $entity = $this->entities->resolve($id);

        if (!$this->permissions->canView($entity)) return "";

        return $this->custom(array(
            "label" => $entity->getTitle(),
            "url" => route("cruddy.index", array($id)),
            "class" => "entity",
        ));
    }

    protected function wrap($value)
    {
        return "<li>{$value}</li>";
    }
}