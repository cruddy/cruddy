<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Entity;

/**
 * Primary field type.
 *
 * @since 1.0.0
 */
class Incrementing extends Integer
{
    /**
     * @param Entity $form
     * @param string $id
     */
    public function __construct(Entity $form, $id)
    {
        parent::__construct($form, $id);

        $this->hide()->disable();
    }

    /**
     * @inheritDoc
     */
    public function setModelValue($model, $value)
    {
        // Disable primary key from altering value
    }

    /**
     * {@inheritdoc}
     *
     * We will check for actual match rather than partial.
     */
    public function applyKeywordsFilter(Builder $builder, array $keywords)
    {
        foreach ($keywords as $keyword) {
            if (is_numeric($keyword)) {
                $builder->orWhere($this->getModelAttributeName(), '=', $keyword);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getRules($modelKey)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * Unique is forced here.
     */
    public function toArray()
    {
        return [ 'unique' => true ] + parent::toArray();
    }
}