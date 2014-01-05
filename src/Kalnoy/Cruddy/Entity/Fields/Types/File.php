<?php namespace Kalnoy\Cruddy\Entity\Fields\Types;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Entity\Attribute\Attribute;
use Kalnoy\Cruddy\Entity\Columns\ColumnInterface;
use Kalnoy\Cruddy\Entity\Fields\AbstractField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Str;

class File extends AbstractField implements ColumnInterface {

    /**
     * Whether there going to be a few files.
     *
     * @var bool
     */
    public $multiple = false;

    /**
     * What kind of files the field accepts.
     *
     * @var string
     */
    public $accepts;

    /**
     * @inheritdoc
     *
     * @param Eloquent $model
     *
     * @return array|string
     */
    public function value(Eloquent $model)
    {
        $value = parent::value($model);

        if ($this->multiple) return is_array($value) ? $value : [];

        return $value === null ? "" : $value;
    }

    /**
     * @inheritdoc
     *
     * @param array|string $value
     *
     * @return array|string
     */
    public function process($value)
    {
        if (empty($value)) return $this->multiple ? [] : "";

        return $this->multiple ? (array)$value : $value;
    }

    /**
     * @inheritdoc
     *
     * @param  Builder $builder
     * @param          $direction
     *
     * @return $this
     */
    public function applyOrder(Builder $builder, $direction)
    {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @param  Builder $query
     * @param  mixed $data
     * @param string $boolean
     *
     * @return $this
     */
    public function applyConstraints(Builder $query, $data, $boolean = 'and')
    {
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isSearchable()
    {
        return false;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isSortable()
    {
        return false;
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function isFilterable()
    {
        return false;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function toArray()
    {
        return parent::toArray() + [
            'multiple' => $this->multiple,
            'accepts' => $this->accepts,
        ];
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    function getJavaScriptClass()
    {
        return 'File';
    }
}