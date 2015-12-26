<?php namespace Kalnoy\Cruddy\Schema\Fields\Types;

use Illuminate\Database\Query\Builder;
use Kalnoy\Cruddy\Entity;

/**
 * Primary field type.
 *
 * @since 1.0.0
 */
class Primary extends StringField
{

    /**
     * @param Entity $entity
     * @param string $id
     */
    public function __construct(Entity $entity, $id)
    {
        parent::__construct($entity, $id);

        $this->hide()->disable();
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
                $builder->orWhere($this->id, '=', $keyword);
            }
        }
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