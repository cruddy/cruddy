<?php

namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Carbon\Carbon;

/**
 * Date and time field.
 * 
 * @since 1.0.0
 */
class DateTime extends BaseTextField {

    /**
     * {@inheritdoc}
     */
    protected $class = 'DateTime';

    /**
     * {@inheritdoc}
     */
    protected $type = 'datetime';

    /**
     * {@inheritdoc}
     */ 
    protected $filterType = self::FILTER_NONE;

    /**
     * {@inheritdoc}
     */
    protected $inputType = 'text';

    /**
     * The format.
     *
     * @var string
     */
    public $format = "DD.MM.YYYY HH:mm";

    /**
     * {@inheritdoc}
     *
     * @return \Carbon\Carbon
     */
    public function process($value)
    {
        return empty($value) ? null : Carbon::createFromTimestamp($value);
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function extract(Eloquent $model)
    {
        $value = parent::extract($model);

        if ($value === null) return null;

        if (!$value instanceof Carbon) $value = new Carbon($value);

        return $value->getTimestamp();
    }

    /**
     * The date format.
     *
     * {@link http://momentjs.com/docs/#/displaying/format/ Format Options}.
     *
     * @param string $value
     *
     * @return $this
     */
    public function format($value)
    {
        $this->format = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return ['format' => $this->format] + parent::toArray();
    }
}