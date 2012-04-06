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

class UnitOfWork
{
    const STATUS_NEW     = 'new';
    const STATUS_CLEAN   = 'clean';
    const STATUS_DIRTY   = 'dirty';
    const STATUS_DELETED = 'deleted';


    /**
     * @var array
     */
    private $entities = array();


    /**
     * @param Entity $entity
     * @return UnitOfWork
     */
    public function registerNew(Entity $entity)
    {
        return $this->registerEntity($entity, self::STATUS_NEW);
    }


    /**
     * @param Entity $entity
     * @return UnitOfWork
     */
    public function registerClean(Entity $entity)
    {
        return $this->registerEntity($entity, self::STATUS_CLEAN);
    }


    /**
     * @param Entity $entity
     * @return UnitOfWork
     */
    public function registerDirty(Entity $entity)
    {
        return $this->registerEntity($entity, self::STATUS_DIRTY);
    }


    /**
     * @param Entity $entity
     * @return UnitOfWork
     */
    public function registerDeleted(Entity $entity)
    {
        return $this->registerEntity($entity, self::STATUS_DELETED);
    }


    /**
     * @param Entity $entity
     * @param string $status
     * @return UnitOfWork
     */
    private function registerEntity(Entity $entity, $status)
    {
        $this->entities[$this->getHash($entity)] = array(
            $entity,
            $status
        );

        return $this;
    }


    /**
     * @param Entity $entity
     * @return string
     */
    private function getHash(Entity $entity)
    {
        return spl_object_hash($entity);
    }
}