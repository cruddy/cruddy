<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Filters;

class Input extends BaseFilter
{
    /**
     * @var string
     */
    protected $type = 'text';

    /**
     * @var string
     */
    public $operator = '=';

    /**
     * @param $query
     * @param $value
     */
    public function apply($query, $value)
    {
        if ($this->callback) {
            call_user_func($this->callback, $query, $value);
        } else {
            $query->where($this->id, $this->operator, $value);
        }
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function operator($value)
    {
        $this->operator = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return array_merge(parent::getConfig(), [
            'type' => $this->type,
        ]);
    }

    /**
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Filters.Input';
    }
}