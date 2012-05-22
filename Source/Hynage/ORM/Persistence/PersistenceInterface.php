<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM\Persistence;
use Hynage\ORM\Entity,
    Hynage\ORM\EntityManager;

interface PersistenceInterface
{
    public function beginTransaction();
    public function hasTransactionStarted();
    public function rollBack();
    public function commit();
    
    public function store(Entity $entity);
    public function delete(Entity $entity);
    public function findOne($entityType, $pkValue);
    public function findOneBy($entityType, array $constraints);
    public function findBy($entityType, array $constraints, $orderBy = null, $limit = null, $offset = null);
    public function query($entityType, $query, array $params = array());
}