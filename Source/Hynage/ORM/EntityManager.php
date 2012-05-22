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
    Hynage\ORM\Entity\Proxy,
    Hynage\ORM\Persistence\PersistenceInterface,
    Hynage\Reflection\ReflectionClass;

class EntityManager
{
    /**
     * @var array
     */
    private $persisters = array();

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


    public function __construct()
    {
        $this->entitiesPool = new \SplObjectStorage();
    }


    /**
     * @param string $name
     * @param Persistence\PersistenceInterface $persister
     * @return \Hynage\ORM\EntityManager
     */
    public function addPersister($name, PersistenceInterface $persister)
    {
        $this->persisters[$name] = $persister;
        return $this;
    }


    /**
     * @param string $name
     * @return bool
     */
    public function hasPersister($name)
    {
        return array_key_exists($name, $this->persisters);
    }


    /**
     * @param string $name
     * @return Persistence\PersistenceInterface $persister
     */
    public function getPersister($name)
    {
        if (!$this->hasPersister($name)) {
            throw new \OutOfBoundsException("No persister with name '$name' available.");
        }

        return $this->persisters[$name];
    }


    /**
     * @param \Closure $formatter
     * @return \Hynage\ORM\EntityManager
     */
    public function setEntityNameFormatter(\Closure $formatter)
    {
        $this->entityNameFormatter = $formatter;
        return $this;
    }


    /**
     * @param \Closure $formatter
     * @return \Hynage\ORM\EntityManager
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
     * @param \Hynage\ORM\Entity $entity
     * @return \Hynage\ORM\EntityManager
     */
    private function addEntity(Entity $entity)
    {
        $this->entitiesPool->attach($entity);

        $this->addProxies($entity);

        return $this;
    }


    /**
     * @param \Hynage\ORM\Entity $entity
     * @return bool
     */
    private function hasEntity(Entity $entity)
    {
        return $this->entitiesPool->contains($entity);
    }


    /**
     * @param \Hynage\ORM\Entity $entity
     * @return \Hynage\ORM\EntityManager
     */
    public function removeEntity(Entity $entity)
    {
        if ($this->hasEntity($entity)) {
            $this->entitiesPool->detach($entity);
        }

        return $this;
    }


    /**
     * @param \Hynage\ORM\Entity $entity
     * @return \Hynage\ORM\EntityManager
     */
    private function addProxies(Entity $entity)
    {
        $reflectionClass = new ReflectionClass($entity::getClassNameOfEntityDefinition());

        foreach ($reflectionClass->getProperties(\ReflectionMethod::IS_PROTECTED) as $property) {
            $definition = $property->getAnnotation('HynageRelation');
            if (!is_array($definition)) {
                continue;
            }

            $proxy = new Proxy($this, $entity, $definition['class'], $definition['local'], $definition['foreign'], $definition['type']);
            $entity->setProxy($property->name, $proxy);
        }

        return $this;
    }


    /**
     * @param string $entityType
     * @param string|array $primaryKey
     * @return \Hynage\ORM\Entity|false
     */
    public function findEntity($entityType, $primaryKey)
    {
        $entityType = $this->formatEntityName($entityType);
        $persisterName = $entityType::getPersisterName();

        $entity = $this->getPersister($persisterName)->findOne($entityType, $primaryKey);

        if ($entity) {
            $this->addEntity($entity);
        }

        return $entity;
    }


    /**
     * @param string $entityType
     * @param array $constraints
     * @return \Hynage\ORM\Entity|false
     */
    public function findEntityBy($entityType, array $constraints)
    {
        $entityType = $this->formatEntityName($entityType);
        $persisterName = $entityType::getPersisterName();

        $entity = $this->getPersister($persisterName)->findOneBy($entityType, $constraints);

        if ($entity) {
            $this->addEntity($entity);
        }

        return $entity;
    }


    /**
     * @param string $entityType
     * @param array $constraints
     * @param string|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return \Hynage\ORM\EntityCollection
     */
    public function findEntitiesBy($entityType, array $constraints, $orderBy = null, $limit = null, $offset = null)
    {
        $entityType = $this->formatEntityName($entityType);
        $persisterName = $entityType::getPersisterName();

        $entities = $this->getPersister($persisterName)->findBy($entityType, $constraints, $orderBy, $limit, $offset);

        foreach ($entities as $entity) {
            $this->addEntity($entity);
        }

        return $entities;
    }


    /**
     * @param string $entityType
     * @param string $query
     * @param array $params
     * @return \Hynage\ORM\EntityCollection
     */
    public function queryEntities($entityType, $query, array $params = array())
    {
        $entityType = $this->formatEntityName($entityType);
        $persisterName = $entityType::getPersisterName();

        $entities = $this->getPersister($persisterName)->query($entityType, $query, $params);

        foreach ($entities as $entity) {
            $this->addEntity($entity);
        }

        return $entities;
    }


    /**
     * @param \Hynage\ORM\Entity $entity
     * @return \Hynage\ORM\EntityManager
     */
    public function persist(Entity $entity)
    {
        $persisterName = $entity::getPersisterName();
        $persister = $this->getPersister($persisterName);
        
        if (!$persister->hasTransactionStarted()) {
            $persister->beginTransaction();
        }
        
        $persister->store($entity);
        
        $this->addEntity($entity);

        return $this;
    }


    /**
     * @param \Hynage\ORM\Entity $entity
     * @return \Hynage\ORM\EntityManager
     */
    public function delete(Entity $entity)
    {
        $persisterName = $entity::getPersisterName();
        $persister = $this->getPersister($persisterName);
                
        if (!$persister->hasTransactionStarted()) {
            $persister->beginTransaction();
        }
        
        $persister->delete($entity);
        
        $this->removeEntity($entity);

        return $this;
    }


    /**
     * @param string|null $persisterName    If null, all persisters will be flushed
     * @return \Hynage\ORM\EntityManager
     */
    public function flush($persisterName = null)
    {
        if ($persisterName !== null) {
            $this->getPersister($persisterName)->commit();
        } else {
            foreach ($this->persisters as $persister) {
                $persister->commit();
            }
        }

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