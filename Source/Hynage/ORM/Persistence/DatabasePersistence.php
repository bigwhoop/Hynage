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
    Hynage\ORM\EntityManager,
    Hynage\ORM\EntityCollection,
    Hynage\ORM\EntityProxyCollection,
    Hynage\ORM\Entity\Field,
    Hynage\ORM\Entity\Proxy,
    Hynage\Database\Connection,
    Hynage\Reflection\ReflectionClass;

class DatabasePersistence implements PersistenceInterface
{
    /**
     * @var \Hynage\Database\Connection
     */
    private $connection = null;


    /**
     * @param \Hynage\Database\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->setConnection($connection);
    }


    /**
     * @param \Hynage\Database\Connection $connection
     * @return \Hynage\ORM\EntityManager
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }


    /**
     * @return \Hynage\Database\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }


    /**
     * @param string $entityType
     * @param scalar|array $values
     * @return \Hynage\ORM\Entity|false
     */
    public function findOne($entityType, $values)
    {
        if (!is_array($values)) {
            $values = array($values);
        }

        $pks = $entityType::getPrimaryKeyFields();

        if (count($pks) != count($values)) {
            throw new \InvalidArgumentException("The given value(s) do not match the number of primary keys of '$entityType'.");
        }

        $constraints = array();
        foreach ($pks as $pk) {
            $constraints[$pk->getName()] = array_shift($values);
        }

        return $this->findOneBy($entityType, $constraints);
    }


    /**
     * @param string $entityType
     * @param array $constraints
     * @return \Hynage\ORM\Entity|false
     */
    public function findOneBy($entityType, array $constraints)
    {
        $tableName = $entityType::getTableName();

        $db = $this->getConnection();

        $placeholders = array();
        foreach (array_keys($constraints) as $fieldName) {
            $field = $entityType::getFieldByProperty($fieldName);
            if ($field) {
                $fieldName = $field->getName();
            }

            $fieldName = trim($fieldName);

            if (false === strpos($fieldName, ' ')) {
                $fieldName = "`$fieldName` = ?";
            }

            $placeholders[] = $fieldName;
        }

        $sql = 'SELECT * '
             . 'FROM `%s` '
             . 'WHERE %s '
             . 'LIMIT 1';

        $sql = sprintf(
            $sql,
            $tableName,
            join(' AND ', $placeholders)
        );

        $values = array_values($constraints);

        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        return $this->hydrate($entityType, $stmt, true);
    }


    /**
     * @param string $entityType
     * @param array $constraints
     * @param string|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return \Hynage\ORM\EntityCollection
     */
    public function findBy($entityType, array $constraints, $orderBy = null, $limit = null, $offset = null)
    {
        $tableName = $entityType::getTableName();

        $db = $this->getConnection();

        $placeholders = array();
        foreach (array_keys($constraints) as $fieldName) {
            $field = $entityType::getFieldByProperty($fieldName);
            if ($field) {
                $fieldName = $field->getName();
            }

            $fieldName = trim($fieldName);

            if (false === strpos($fieldName, ' ')) {
                $fieldName = "`$fieldName` = ?";
            }

            $placeholders[] = $fieldName;
        }

        $values = array_values($constraints);

        $sql = 'SELECT * '
             . 'FROM `%s` '
             . 'WHERE %s ';

        if (is_string($orderBy)) {
            $sql .= "ORDER BY $orderBy ";
        }

        if (is_int($limit)) {
            if (is_int($offset)) {
                $sql .= "LIMIT $offset, " . (int)$limit;
            } else {
                $sql .= 'LIMIT ' . (int)$limit;
            }
        }

        $sql = sprintf(
            $sql,
            $tableName,
            count($placeholders) ? join(' AND ', $placeholders) : '1 = 1'
        );

        $stmt = $db->prepare($sql);
        $stmt->execute($values);

        return $this->hydrate($entityType, $stmt);
    }

    /**
     * @param string $entityType
     * @param string $sql
     * @param array $params
     * @return \Hynage\ORM\EntityCollection
     */
    public function query($entityType, $sql, array $params = array())
    {
        $db = $this->getConnection();

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $this->hydrate($entityType, $stmt);
    }


    /**
     * @param \Hynage\ORM\Entity|string $entityType
     * @param array|\PDOStatement $data
     * @param bool $singleEntity
     * @return \Hynage\ORM\EntityCollection|Entity|false
     * @throws \InvalidArgumentException
     */
    private function hydrate($entityType, $data, $singleEntity = false)
    {
        if ($data instanceof \PDOStatement) {
            $data = $data->fetchAll(\PDO::FETCH_ASSOC);
        } elseif (!is_array($data)) {
            throw new \InvalidArgumentException('First argument must either be an array or an instance of \PDOStatement.');
        }

        if ($entityType instanceof Entity) {
            $entityType = get_class($entityType);
        } elseif (!is_string($entityType)) {
            throw new \InvalidArgumentException('Entity type must be a string.');
        }

        $entities = new EntityCollection(array(), $entityType);

        foreach ((array)$data as $values) {
            if (!is_array($values)) {
                continue;
            }

            $obj = new $entityType();
            $obj->setIsPersistent(true);
            $obj->populate($entityType::getFieldDefinitions(), $values);

            $entities->add($obj);
        }

        // Only one Entity is expected. Or false if none found.
        if ($singleEntity) {
            return count($entities) ? $entities->get(0) : false;
        }

        return $entities;
    }


    /**
     * @param \Hynage\ORM\Entity $entity
     * @return DatabasePersistence
     */
    public function store(Entity $entity)
    {
        $db = $this->getConnection();

        $tableName = $entity::getTableName();

        $values = array();
        $autoIncrementField = null;

        foreach ($entity::getFieldDefinitions() as $field) {
            if ($field->isAutoIncrement()) {
                $autoIncrementField = $field;
                continue;
            }

            $value = $entity->getValue($field);

            // Skip non-NOT-NULL-fields with NULL value
            if (null === $value && !$field->isNotNull()) {
                continue;
            }

            // Set default value
            if (null === $value) {
                $value = $field->getDefaultValue();
            }

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $values[$field->getName()] = $value;
        }

        // Update
        if ($entity->isPersistent()) {
            $placeholders = array();
            foreach (array_keys($values) as $field) {
                $placeholders[] = "`$field` = ?";
            }

            $sql = sprintf(
                "UPDATE `%s` SET %s WHERE %s LIMIT 1",
                $tableName,
                join(', ', $placeholders),
                $this->buildWhereForPrimaryKeyFields($entity)
            );

            foreach ($this->getPrimaryKeyFields($entity) as $pk) {
                $values[$pk->getName()] = $entity->getValue($pk);
            }

            $stmt = $db->prepare($sql);
            $stmt->execute(array_values($values));
        }

        // Insert
        else {
            $placeholders = array_pad(array(), count($values), '?');

            $sql = sprintf(
                "INSERT INTO `%s` (%s) VALUES (%s)",
                $tableName,
                '`' . join('`, `', array_keys($values)) . '`',
                join(', ', $placeholders)
            );

            $stmt = $db->prepare($sql);
            $stmt->execute(array_values($values));

            if (null !== $autoIncrementField) {
                $entity->setValue($autoIncrementField, $db->getAdapter()->lastInsertId());
            }

            $entity->setIsPersistent(true);
        }

        return $this;
    }


    /**
     * @param \Hynage\ORM\Entity $entity
     * @return DatabasePersistence
     */
    public function delete(Entity $entity)
    {
        $db = $this->getConnection();

        $tableName = $entity::getTableName();

        $sql = sprintf(
            "DELETE FROM `%s` WHERE %s LIMIT 1",
            $tableName,
            $this->buildWhereForPrimaryKeyFields($entity)
        );

        $params = array();
        foreach ($entity::getPrimaryKeyFields($entity) as $pk) {
            $params[$pk->getName()] = $entity->getValue($pk);
        }
        $params = array_values($params);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $entity->setIsPersistent(false);

        return $this;
    }


    /**
     * @param \Hynage\ORM\Entity|string $entity
     * @return string
     * @throws \LogicException
     */
    public function buildWhereForPrimaryKeyFields($entity)
    {
        $pks = $entity::getPrimaryKeyFields();

        if (!count($pks)) {
            throw new \LogicException('There is no primary key defined.');
        }

        $wheres = array();
        foreach ($pks as $pk) {
            $wheres[] = sprintf('`%s` = ?', $pk->getName());
        }

        return ' (' . join(' AND ', $wheres) . ') ';
    }
}