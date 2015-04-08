<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\BaseForm;
use Kalnoy\Cruddy\Contracts\Filter;
use Kalnoy\Cruddy\Helpers;
use Kalnoy\Cruddy\Schema\Fields\BaseInput;

/**
 * The field for displaying select box.
 *
 * @property string $prompt
 * @method $this prompt(string $value)
 *
 * @since 1.0.0
 */
class Enum extends BaseInput implements Filter {

    /**
     * @var mixed
     */
    protected $items;

    /**
     * @param BaseForm $entity
     * @param string $id
     * @param $items
     */
    public function __construct(BaseForm $entity, $id, $items)
    {
        parent::__construct($entity, $id);

        $this->items = $items;
    }

    /**
     * The name of the JavaScript class that is used to render this field.
     *
     * @return string
     */
    protected function modelClass()
    {
        return 'Cruddy.Fields.Enum';
    }

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        $items = $this->getItems();

        if ( ! isset($items[$value])) return null;

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(Builder $query, $data)
    {
        $query->where($this->id, '=', $data);
    }

    /**
     * Translate items if possible.
     *
     * @param array $items
     *
     * @return array
     */
    protected function translateItems($items)
    {
        foreach ($items as $key => $value)
        {
            $items[$key] = Helpers::tryTranslate($value);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'prompt' => Helpers::tryTranslate($this->get('prompt')),
            'items' => $this->translateItems(value($this->items)),

        ] + parent::toArray();
    }

    /**
     * @return array
     */
    protected function getItems()
    {
        if ($this->items instanceof \Closure) $this->items = value($this->items);

        return $this->items;
    }
}