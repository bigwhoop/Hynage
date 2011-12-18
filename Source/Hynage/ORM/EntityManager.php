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
    Hynage\ORM\EntityCollection,
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
     * @var null|\Closure
     */
    private $entityNameFormatter = null;

    /**
     * @var null|\Closure
     */
    private $repositoryNameFormatter = null;


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
     * @param \Closure $formatter
     * @return EntityManager
     */
    public function setEntityNameFormatter(\Closure $formatter)
    {
        $this->entityNameFormatter = $formatter;
        return $this;
    }


    /**
     * @param \Closure $formatter
     * @return EntityManager
     */
    public function setRepositoryNameFormatter(\Closure $formatter)
    {
        $this->repositoryNameFormatter = $formatter;
        return $this;
    }


    /**
     * @param string $name
     * @return string
     */
    private function formatEntityName($name)
    {
        if ($this->entityNameFormatter) {
            $name = (string)call_user_func($this->entityNameFormatter, $name);
        }

        return $name;
    }


    /**
     * @param string $name
     * @return string
     */
    private function formatRepositoryName($name)
    {
        if ($this->repositoryNameFormatter) {
            $name = (string)call_user_func($this->repositoryNameFormatter, $name);
        }

        return $name;
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
        $entityType = $this->formatEntityName($entityType);

        $entity = $this->getPersister()->findOne($entityType, $primaryKey);

        if ($entity) {
            $this->addEntity($entity);
        }

        return $entity;
    }


    /**
     * @param string $entityType
     * @param array $constraints
     * @return Entity|false
     */
    public function findEntityBy($entityType, array $constraints)
    {
        $entityType = $this->formatEntityName($entityType);

        $entity = $this->getPersister()->findOneBy($entityType, $constraints);

        if ($entity) {
            $this->addEntity($entity);
        }

        return $entity;
    }


    /**
     * @param string $entityType
     * @param array $constraints
     * @param int|null $limit
     * @param int|null $offset
     * @return EntityCollection
     */
    public function findEntitiesBy($entityType, array $constraints, $limit = null, $offset = null)
    {
        $entityType = $this->formatEntityName($entityType);

        $entities = $this->getPersister()->findBy($entityType, $constraints, $limit, $offset);

        foreach ($entities as $entity) {
            $this->addEntity($entity);
        }

        return $entities;
    }


    /**
     * @param Entity $entity
     * @return EntityManager
     */
    public function persist(Entity $entity)
    {
        $this->getPersister()->store($entity);
        $this->addEntity($entity);

        return $this;
    }


    /**
     * @param Entity $entity
     * @return EntityManager
     */
    public function delete(Entity $entity)
    {
        $this->getPersister()->delete($entity);
        $this->removeEntity($entity);

        return $this;
    }


    /**
     * @param string $repositoryType
     * @return RepositoryInterface
     * @throws \LogicException
     */
    public function getRepository($repositoryType)
    {
        $entityType = $this->formatRepositoryName($repositoryType);

        $obj = new $entityType($this);

        if (!$obj instanceof EntityRepository) {
            throw new \LogicException('Repository "' . get_class($obj) . '" must implement RepositoryInterface.');
        }

        return $obj;
    }
}