<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Schema\Fields\BaseField;

class Enum extends BaseField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'Enum';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'enum';

    /**
     * @inheritdoc
     *
     * @var string
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
     * Apply constraints to the query builder.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed                              $data
     *
     * @return $this
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
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return
        [
            'prompt' => \Kalnoy\Cruddy\try_trans($this->prompt),
            'items' => \value($this->items),

        ] + parent::toArray();
    }
}