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
 * @method $this prompt(StringField $value)
 *
 * @property bool $multiple
 * @method $this multiple(bool $value = true)
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
        $value = $this->parse($value);

        return $this->multiple ? $value : reset($value);
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilterConstraint(Builder $query, $data)
    {
        if ($this->multiple or ! ($data = $this->parse($data))) return;

        $query->whereNested(function ($inner) use ($data)
        {
            foreach ($data as $key)
            {
                $inner->orWhere($this->id, '=', $key);
            }
        });
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
            'items' => $this->translateItems($this->getItems()),
            'multiple' => $this->get('multiple', false),

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

    /**
     * Parse value and return array of valid items.
     *
     * @param $value
     *
     * @return array
     */
    protected function parse($value)
    {
        if (empty($value)) return [];

        if (is_string($value)) $value = explode(',', $value);

        $items = array_keys($this->getItems());

        return array_values(array_intersect($items, (array)$value));
    }

}