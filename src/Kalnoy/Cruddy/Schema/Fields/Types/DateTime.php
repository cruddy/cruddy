<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Kalnoy\Cruddy\Schema\Fields\BaseTextField;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Carbon\Carbon;

class DateTime extends BaseTextField {

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $class = 'DateTime';

    /**
     * @inheritdoc
     *
     * @var string
     */
    protected $type = 'datetime';

    /**
     * @inheritdoc
     *
     * @var string
     */ 
    protected $filterType = self::FILTER_NONE;

    /**
     * The input type.
     *
     * @var string
     */
    protected $inputType = 'text';

    /**
     * The format.
     *
     * @var string
     */
    public $format = "DD.MM.YYYY HH:mm";

    /**
     * @inheritdoc
     *
     * @param mixed $value
     *
     * @return \Carbon\Carbon
     */
    public function process($value)
    {
        return empty($value) ? null : Carbon::createFromTimestamp($value);
    }

    /**
     * @inheritdoc
     *
     * @param \Illuminate\Database\Eloquent\Model $model
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
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return ['format' => $this->format] + parent::toArray();
    }
}