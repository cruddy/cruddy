<?php namespace Kalnoy\Cruddy\Entity\Related\Types;

use Kalnoy\Cruddy\Entity\Related\AbstractRelated;

class One extends AbstractRelated {

    /**
     * The id of related entity. By default this is plural form of the related id.
     *
     * @var string
     */
    public $reference;

    protected function getForeignKey()
    {
        return $this->relation()->getPlainForeignKey();
    }

    protected function resolveRelated()
    {
        $reference = $this->reference ?: str_plural($this->id);

        return $this->resolveEntity($reference);
    }
}