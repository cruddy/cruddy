<?php namespace Kalnoy\Cruddy\Related\Types;

use Kalnoy\Cruddy\Related\AbstractRelated;

class One extends AbstractRelated {

    public $reference;

    protected function resolveRelated()
    {
        $reference = $this->reference ?: str_plural($this->id);

        return $this->resolveEntity($reference);
    }
}