<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Schema\Fields\BaseInput;

/**
 * The field for displaying select box.
 * 
 * @since 1.0.0
 */
class Enum extends BaseInput {

    /**
     * {@inheritdoc}
     */
    protected $class = 'Enum';

    /**
     * {@inheritdoc}
     */
    protected $type = 'enum';

    /**
     * {@inheritdoc}
     */
    protected $filterType = self::FILTER_COMPLEX;

    /**
     * @var array|Callable
     */
    public $items;

    /**
     * @var string
     */
    public $prompt;

    /**
     * {@inheritdoc}
     */
    public function process($value)
    {
        $items = value($this->items);

        if ( ! isset($items[$value])) return null;

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(Builder $query, $data)
    {
        $query->where($this->id, $data);

        return $this;
    }

    /**
     * Set prompt value. The value will be translated if it has dots.
     *
     * @param string $value
     *
     * @return $this
     */
    public function prompt($value)
    {
        $this->prompt = $value;

        return $this;
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
            $items[$key] = \Kalnoy\Cruddy\try_trans($value);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return
        [
            'prompt' => \Kalnoy\Cruddy\try_trans($this->prompt),
            'items' => $this->translateItems(value($this->items)),

        ] + parent::toArray();
    }
}