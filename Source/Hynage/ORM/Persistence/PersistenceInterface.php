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
use Hynage\ORM\Entity;

interface PersistenceInterface
{
    public function store(Entity $entity);
    public function delete(Entity $entity);
    public function findOne($entityType, $pkValue);
    public function findOneBy($entityType, array $constraints);
    public function findBy($entityType, array $constraints, $limit = null, $offset = null);
}