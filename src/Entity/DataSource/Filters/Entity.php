<?php

namespace Kalnoy\Cruddy\Entity\DataSource\Filters;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Cruddy\Entity\DataSource\DataSource;
use Kalnoy\Cruddy\Service\GetsValueFromModel;
use Kalnoy\Cruddy\Service\ReferencesEntity;
use Kalnoy\Cruddy\Service\WorksWithRelations;

class Entity extends BaseFilter
{
    use ReferencesEntity, WorksWithRelations, GetsValueFromModel;

    /**
     * Entity constructor.
     *
     * @param DataSource $owner
     * @param $id
     * @param null $refEntityId
     */
    public function __construct(DataSource $owner, $id, $refEntityId = null)
    {
        parent::__construct($owner, $id);

        $this->refEntityId = $refEntityId;
    }

    /**
     * @param $query
     * @param $value
     */
    public function apply($query, $value)
    {
        if ( ! $value = $this->processInput($value)) {
            return;
        }

        if ($this->callback) {
            call_user_func($this->callback, $query, $value);

            return;
        }

        $relation = $this->newRelationQuery();

        if ($relation instanceof BelongsTo) {
            $query->whereIn($relation->getQualifiedForeignKey(), $value);
        }
    }

    /**
     * @param $value
     *
     * @return array
     */
    protected function processInput($value)
    {
        if (is_array($value)) {
            return $value;
        }

        return empty($value) ? [] : explode(',', $value);
    }

    /**
     * @return string
     */
    public function getUIModelClass()
    {
        return 'Cruddy.Filters.Entity';
    }

    /**
     * @return \Kalnoy\Cruddy\Entity\Entity
     */
    public function getEntity()
    {
        return $this->owner->getEntity();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $this->getRefEntity();

        return array_merge(parent::getConfig(), [
            'refEntityId' => $this->getRefEntityId(),
        ]);
    }

    /**
     * @return callback
     */
    protected function getDefaultGetter()
    {
        return null;
    }
}