<?php  namespace Kalnoy\Cruddy\Entity\Related\Types;

class MorphOne extends One {

    public function toArray()
    {
        $rel = $this->relation();

        return parent::toArray() + [
            'morph_type' => $rel->getMorphType(),
            'morph_class' => $rel->getMorphClass(),
        ];
    }

    public function getJavaScriptClass()
    {
        return 'MorphOne';
    }
}