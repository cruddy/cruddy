<?php namespace Kalnoy\Cruddy\Fields\Types;

use Kalnoy\Cruddy\Fields\Input;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Carbon\Carbon;

class DateTime extends Input {

    protected $inputType = "text";

    public $format = "DD.MM.YYYY HH:mm";

    public function process($value)
    {
        return Carbon::createFromTimestamp($value);
    }

    public function value(Eloquent $model)
    {
        $value = parent::value($model);

        if ($value === null) return null;

        if (!$value instanceof Carbon) $value = new Carbon($value);

        return $value->getTimestamp();
    }

    public function toArray()
    {
        return parent::toArray() + array('format' => $this->format);
    }

    public function isFilterable()
    {
        return false;
    }

    /**
     * Get the java script class name.
     *
     * @return string
     */
    public function getJavaScriptClass()
    {
        return 'DateTime';
    }
}