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
    Hynage\ORM\EntityCollection,
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
     * @return EntityManager
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
     * @param scalar|array $value
     * @return Entity|false
     */
    public function findOne($entityType, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        return $this->findWhere($entityType, $this->buildWhereForPrimaryKeyFields($entityType), $value, true);
    }


    /**
     * @param string $entityType
     * @param string $field
     * @param string $value
     * @return Entity|false
     */
    public function findOneBy($entityType, $field, $value)
    {
        if (empty($value)) {
            return false;
        }

        $tableName = $this->getTableName($entityType);

        $db = $this->getConnection();

        $sql = 'SELECT * '
             . 'FROM `%s` '
             . 'WHERE `%s` = ?'
             . 'LIMIT 1';

        $sql = sprintf($sql, $tableName, $field);

        $stmt = $db->prepare($sql);
        $stmt->execute((array)$value);

        return $this->hydrate($entityType, $stmt, true);
    }


    /**
     * @param Entity|string $entityType
     * @param string $sqlWhere
     * @param array $params
     * @param bool $singleEntity
     * @return Entity|EntityCollection|false
     */
    public function findWhere($entityType, $sqlWhere, array $params = array(), $singleEntity = false)
    {
        $db = $this->getConnection();

        $sql = 'SELECT * '
             . 'FROM `%s` '
             . 'WHERE %s';

        $sql = sprintf($sql, $this->getTableName($entityType), $sqlWhere);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $this->hydrate($entityType, $stmt, $singleEntity);
    }


    /**
     * @param Entity|string $entityType
     * @param array|\PDOStatement $data
     * @param bool $singleEntity
     * @return EntityCollection|Entity|false
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

        $entities = new EntityCollection();

        foreach ((array)$data as $values) {
            if (!is_array($values)) {
                continue;
            }

            $obj = new $entityType();
            $obj->setIsPersistent(true);
            $obj->populate($values);

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

        $tableName = $this->getTableName($entity);

        $values = array();
        $autoIncrementField = null;

        foreach ($this->getFieldDefinitions($entity) as $field) {
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

        $tableName = $this->getTableName($entity);

        $sql = sprintf(
            "DELETE FROM `%s` WHERE %s LIMIT 1",
            $tableName,
            $this->buildWhereForPrimaryKeyFields($entity)
        );

        $params = array();
        foreach ($this->getPrimaryKeyFields($entity) as $pk) {
            $params[$pk->getName()] = $entity->getValue($pk);
        }
        $params = array_values($params);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $entity->setIsPersistent(false);

        return $this;
    }


    /**
     * Return the table name defined by the '@HynageTable' annotation.
     *
     * @param \Hynage\ORM\Entity|string $entityType
     * @return string
     */
    public function getTableName($entityType)
    {
        $reflectionClass = new ReflectionClass($entityType::getClassNameOfEntityDefinition());
        return $reflectionClass->getAnnotation('HynageTable');
    }


    /**
     * @param \Hynage\ORM\Entity|string $entity
     * @return array
     */
    public function getPrimaryKeyFields($entity)
    {
        $pks = array();

        foreach ($this->getFieldDefinitions($entity) as $field) {
            if ($field->isPrimary()) {
                $pks[] = $field;
            }
        }

        return $pks;
    }


    /**
     * @param \Hynage\ORM\Entity|string $entity
     * @return array
     */
    public function getFieldDefinitions($entity)
    {
        $fields = array();

        $reflectionClass = new ReflectionClass($entity::getClassNameOfEntityDefinition());

        foreach ($reflectionClass->getProperties(\ReflectionMethod::IS_PROTECTED) as $property) {
            $definition = $property->getAnnotation('HynageColumn');
            if (!is_array($definition)) {
                continue;
            }

            $propertyName = $property->name;

            $name = isset($definition['name'])
                  ? $definition['name']
                  : ltrim($property->name, '_');

            $type = isset($definition['type'])
                  ? strtoupper($definition['type'])
                  : 'VARCHAR';

            $length = isset($definition['length'])
                    ? (int)$definition['length']
                    : null;

            $attributes = array();
            $attributes['unsigned']      = $property->hasAnnotation('HynageColumnUnsigned');
            $attributes['notNull']       = $property->hasAnnotation('HynageColumnNotNull');
            $attributes['autoIncrement'] = $property->hasAnnotation('HynageColumnAutoIncrement');
            $attributes['primary']       = $property->hasAnnotation('HynageColumnPrimary');

            if ($property->hasAnnotation('HynageColumnDefault')) {
                $attributes['default'] = $property->getAnnotation('HynageColumnDefault');
            }

            $fields[] = new Entity\Field($name, $propertyName, $type, $length, $attributes);
        }

        return $fields;
    }


    /**
     * @param \Hynage\ORM\Entity|string $entity
     * @return string
     * @throws \LogicException
     */
    public function buildWhereForPrimaryKeyFields($entity)
    {
        $pks = $this->getPrimaryKeyFields($entity);

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