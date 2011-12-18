<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM;
use Hynage\ORM\Entity,
    Hynage\ORM\Persistence\PersistenceInterface;

class EntityManager
{
    /**
     * @var Persistence\PersistenceInterface
     */
    private $persister = null;

    /**
     * @var \SplObjectStorage
     */
    private $entitiesPool = null;


    /**
     * @param Persistence\PersistenceInterface $persister
     */
    public function __construct(PersistenceInterface $persister)
    {
        $this->entitiesPool = new \SplObjectStorage();
        $this->setPersister($persister);
    }


    /**
     * @param Persistence\PersistenceInterface $persister
     * @return EntityManager
     */
    public function setPersister(PersistenceInterface $persister)
    {
        $this->persister = $persister;
        return $this;
    }


    /**
     * @return Persistence\PersistenceInterface $persister
     */
    public function getPersister()
    {
        return $this->persister;
    }
    

    /**
     * @param Entity $entity
     * @return EntityManager
     */
    private function addEntity(Entity $entity)
    {
        $this->entitiesPool->attach($entity);
        return $this;
    }


    /**
     * @param Entity $entity
     * @return bool
     */
    private function hasEntity(Entity $entity)
    {
        return $this->entitiesPool->contains($entity);
    }


    /**
     * @param Entity $entity
     * @return EntityManager
     */
    public function removeEntity(Entity $entity)
    {
        if ($this->hasEntity($entity)) {
            $this->entitiesPool->detach($entity);
        }

        return $this;
    }


    /**
     * @param string $entityType
     * @param string|array $primaryKey
     * @return Entity|false
     */
    public function findEntity($entityType, $primaryKey)
    {
        $entity = $this->getPersister()->findOne($entityType, $primaryKey);

        if ($entity) {
            $this->addEntity($entity);
        }

        return $entity;
    }


    /**
     * @param string $entityType
     * @param string $field
     * @param string $value
     * @return Entity|false
     */
    public function findEntityBy($entityType, $field, $value)
    {
        $entity = $this->getPersister()->findOneBy($entityType, $field, $value);

        if ($entity) {
            $this->addEntity($entity);
        }

        return $entity;
    }


    /**
     * @param Entity $entity
     * @return EntityManager
     */
    public function persist(Entity $entity)
    {
        $this->persister->store($entity);
        $this->addEntity($entity);

        return $this;
    }


    /**
     * @param Entity $entity
     * @return EntityManager
     */
    public function delete(Entity $entity)
    {
        $this->persister->delete($entity);
        $this->removeEntity($entity);

        return $this;
    }
}