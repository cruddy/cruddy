<?php

namespace Kalnoy\Cruddy\Service;

use Kalnoy\Cruddy\Entity\Entity;
use Kalnoy\Cruddy\Entity\Repository;

trait ReferencesEntity
{
    /**
     * The entity that this relation refers to.
     *
     * @var Entity
     */
    private $refEntity;

    /**
     * @var string
     */
    protected $refEntityId;

    /**
     * Get referenced entity instance.
     *
     * @return Entity
     */
    public function getRefEntity()
    {
        if ($this->refEntity !== null) {
            return $this->refEntity;
        }

        $this->refEntity = $this->getEntitiesRepository()
                                ->resolve($this->getRefEntityId());

        return $this->refEntity;
    }

    /**
     * @return string
     */
    public function getRefEntityId()
    {
        return $this->refEntityId ?: $this->getId();
    }

    /**
     * @return Repository
     */
    public function getEntitiesRepository()
    {
        return app(Repository::class);
    }
}