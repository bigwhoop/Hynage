<?php
namespace Hynage\ORM\Model;
use Hynage,
    Hynage\Reflection,
    Hynage\Database\Connection as Connection;

abstract class Record implements ExportStrategy\Exportable
{
    /**
     * @bool
     */
    protected $_isPersistent = false;

    
    /**
     * Find records by an SQL statement
     * 
     * @param string $sql
     * @param array $params
     * @param bool $singleRecord
     * @return array|Hynage\ORM\Model\Record|false
     */
    public static function find($sql, array $params = array(), $singleRecord = false)
    {
        $db = Connection::getCurrent();
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return self::_hydrate($stmt, $singleRecord);
    }


    static public function findWhere($sqlWhere, array $params = array(), $singleRecord = false)
    {
        $db = Connection::getCurrent();
        
        $sql = 'SELECT * '
             . 'FROM `%s` '
             . 'WHERE %s';

        $sql = sprintf($sql, static::getTableName(), $sqlWhere);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return static::_hydrate($stmt, $singleRecord);
    }


    static public function findOneBy($field, $value, $load = false)
    {
        if (empty($value)) {
            return false;
        }

        $tableName = static::getTableName();

        static $cache = array();
        $cacheId = $tableName . '-' . $field;

        if ($load || !array_key_exists($cacheId, $cache)) {
            $db = Connection::getCurrent();

            $sql = 'SELECT * '
                 . 'FROM `%s` '
                 . 'WHERE `%s` = ?'
                 . 'LIMIT 1';

            $sql = sprintf($sql, $tableName, $field);

            $stmt = $db->prepare($sql);
            $stmt->execute((array)$value);

            $cache[$cacheId] = self::_hydrate($stmt, true);
        }

        return $cache[$cacheId];
    }
    
    
    /**
     * Find a specific record by its primary key
     * 
     * @param int|array $id
     * @return \Hynage\ORM\Model\Record|false
     */
    public static function findOne($id)
    {
        return static::findWhere(static::buildWhereForPrimaryKeyFields(), array($id));
    }


    /**
     * @throws \LogicException
     * @return string
     */
    public function buildWhereForPrimaryKeyFields()
    {
        $pks = static::getPrimaryKeyFields();

        if (!count($pks)) {
            throw new \LogicException('There is no primary key defined.');
        }

        $wheres = array();
        foreach ($pks as $pk) {
            $wheres[] = sprintf('`%s` = ?', $pk->getName());
        }
        
        return ' (' . join(' AND ', $wheres) . ') ';
    }

    
    
    /**
     * Find all records of this model
     * 
     * @return \Hynage\ORM\Model\RecordCollection
     */
    public static function findAll()
    {
        $db = Connection::getCurrent();
        
        $sql = sprintf(
            'SELECT * FROM `%s`',
            static::getTableName()
        );
        
        $stmt = $db->query($sql);
        
        return self::_hydrate($stmt);
    }
    

    /**
     * Puts an data into Model objects of the called class.
     *
     * @param \PDOStatement|array $data
     * @param bool $singleRecord
     * @return \Hynage\ORM\Model\Record|\Hynage\ORM\Model\RecordCollection
     * @throws \InvalidArgumentException
     */
    protected static function _hydrate($data, $singleRecord = false)
    {
        if ($data instanceof \PDOStatement) {
            $data = $data->fetchAll(\PDO::FETCH_ASSOC);
        } elseif (!is_array($data)) {
            throw new \InvalidArgumentException('First argument must either be an array or an instance of \PDOStatement.');
        }
        
        $records = new RecordCollection();
        $class = get_called_class();
        
        foreach ((array)$data as $values) {
            if (!is_array($values)) {
                continue;
            }
            
            $obj = new $class;
            $obj->_isPersistent = true;
            $obj->populate($values);
            
            $records->add($obj);
        }
        
        // Only one record is expected. Or false if none found.
        if ($singleRecord) {
            return count($records) ? $records->get(0) : false;
        }
        
        return $records;
    }


    static protected function _getBaseClassName()
    {
        $class = new \ReflectionClass(get_called_class());
        while ($class instanceof \ReflectionClass)
        {
            if (false !== strpos($class->getDocComment(), 'HynageTable')) {
                return $class->getName();
            }

            $class = $class->getParentClass();
        }

        throw new Record\InvalidDefinitionException('Could not find class with table/field definition.');
    }
    
    
    /**
     * Return the table name defined by the '@HynageTable' annotation in
     * the class's doc block.
     * 
     * @return string
     */
    public static function getTableName()
    {
        $reflectionClass = new Reflection\ReflectionClass(self::_getBaseClassName());
        return $reflectionClass->getAnnotation('HynageTable');
    }



    
    
    /**
     * Return the primary key field(s)
     * 
     * @return \Hynage\ORM\Model\Record\Field|array|false
     */
    public static function getPrimaryKeyFields()
    {
        $pks = array();

        foreach (self::getFieldDefinitions() as $field) {
            if ($field->isPrimary()) {
                $pks[] = $field;
            }
        }
        
        return $pks;
    }
    
    
    public static function getFieldDefinitions()
    {
        $fields = array();

        $reflectionClass = new Reflection\ReflectionClass(self::_getBaseClassName());
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
                $attributes['default']   = $property->getAnnotation('HynageColumnDefault');
            }
            
            $fields[] = new Record\Field($name, $propertyName, $type, $length, $attributes);
        }
        
        return $fields;
    }
    
    
    /**
     * Generate a CREATE TABLE statement
     * 
     * @return string
     */
    public static function generateCreateTableStatement()
    {
        $tableName = self::getTableName();
        
        // Create table
        $sql = sprintf('CREATE TABLE `%s` (' . PHP_EOL, $tableName);
        
        $primaryKeys = array();
        
        // Add columns
        $reflectionClass = new Reflection\ReflectionClass(self::_getBaseClassName());
        foreach ($reflectionClass->getProperties() as $property) {
            $definition = $property->getAnnotation('HynageColumn');
            if (!is_array($definition)) {
                continue;
            }
            
            $name = isset($definition['name'])
                  ? $definition['name']
                  : ltrim($property->name, '_');
            
            $default = $property->getAnnotation('HynageColumnDefault');
            
            $sql .= sprintf(
                '    `%s` %s%s%s%s%s%s,' . PHP_EOL,
                $name,
                isset($definition['type']) ? strtoupper($definition['type']) : 'VARCHAR',
                isset($definition['length']) ? sprintf('(%d)', $definition['length']) : '',
                $property->hasAnnotation('HynageColumnUnsigned') ? ' UNSIGNED' : '',
                $property->hasAnnotation('HynageColumnNotNull') ? ' NOT NULL' : ' NULL',
                null === $default ? '' : sprintf(' DEFAULT \'%s\'', $default),
                $property->hasAnnotation('HynageColumnAutoIncrement') ? ' AUTO_INCREMENT' : ''
            );
            
            if ($property->hasAnnotation('HynageColumnPrimary')) {
                $primaryKeys[] = sprintf('`%s`', $name);
            }
        }
        
        // Add primary key(s)
        $sql .= sprintf('    PRIMARY KEY (%s)' . PHP_EOL, join(', ', $primaryKeys));
        
        $sql .= ');';
        
        return $sql;
    }


    public function getFieldByName($name)
    {
        foreach (static::getFieldDefinitions() as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return false;
    }


    public function getValue($field)
    {
        if (!$field instanceof Record\Field) {
            $fieldName = $field;
            $field = $this->getFieldByName($field);
            if (!$field) {
                throw new Record\InvalidDefinitionException('No such field: ' . $fieldName);
            }
        }
        
        $property = $field->getProperty();

        if (!isset($this->$property)) {
            throw new Record\InvalidDefinitionException('No such field: ' . $field->getName());
        }

        return $this->$property;
    }


    /**
     * Export this record. Default is array.
     *
     * @param \Hynage\ORM\Model\ExportStrategy\Exporting $strategy
     * @return mixed
     */
    public function export(ExportStrategy\Exporting $strategy = null)
    {
        if (!$strategy) {
            $strategy = new ExportStrategy\ArrayStrategy();
        }

        return $strategy->exportRecord($this);
    }
    
    
    /**
     * Set the properties given by a key/value array
     * 
     * @param array $values
     * @return \Hynage\ORM\Model\Record
     */
    public function populate(array $values)
    {
        $keys = array_keys($values);
        
        foreach (self::getFieldDefinitions() as $field) {
            if (!in_array($field->getName(), $keys)) {
                continue;
            }
            
            $value = $values[$field->getName()];
            
            if (null !== $value || (null === $value && $field->isNotNull())) {
                switch ($field->getType())
                {
                    case 'INTEGER':
                    case 'SMALLINT':
                    case 'MEDIUMINT':
                    case 'BIGINT':
                        $value = (int)$value;
                        break;
                        
                    case 'TINYINT':
                        $value = (bool)$value;
                        break;
                        
                    case 'FLOAT':
                        $value = (float)$value;
                        break;
                        
                    case 'DATETIME':
                        $value = new \DateTime($value);
                        break;
                }
            }
            
            $this->{$field->getProperty()} = $value;
        }
        
        return $this;
    }


    /**
     * @return bool
     */
    public function isPersistent()
    {
        return $this->_isPersistent;
    }
    

    /**
     * @return \Hynage\ORM\Model\Record
     */
    public function save()
    {
        $db = Connection::getCurrent();
        
        $tableName  = static::getTableName();
        
        $values = array();
        $autoIncrementField = null;
        foreach (static::getFieldDefinitions() as $field) {
            if ($field->isAutoIncrement()) {
                $autoIncrementField = $field;
                continue;
            }
            
            $value = $this->{$field->getProperty()};
            
            // Skip non-NOT-NULL-fields with NULL value
            if (null === $value && !$field->isNotNull()) {
                continue;
            }
            
            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
            
            $values[$field->getName()] = $value;
        }
        
        // Insert
        if (!$this->isPersistent()) {
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
                $this->{$autoIncrementField->getProperty()} = $db->getAdapter()->lastInsertId();
            }

            $this->_isPersistent = true;
        }
        
        // Update
        else {
            $placeholders = array();
            foreach (array_keys($values) as $field) {
                $placeholders[] = "`$field` = ?";
            }
            
            $sql = sprintf(
                "UPDATE `%s` SET %s WHERE %s LIMIT 1",
                $tableName,
                join(', ', $placeholders),
                static::buildWhereForPrimaryKeyFields()
            );

            foreach (static::getPrimaryKeyFields() as $pk) {
                $values[$pk->getName()] = $this->{$pk->getProperty()};
            }

            $stmt = $db->prepare($sql);
            $stmt->execute(array_values($values));
        }
        
        return $this;
    }
    
    
    public function delete()
    {
        $db = Connection::getCurrent();
        
        $tableName  = static::getTableName();
        
        $sql = sprintf(
            "DELETE FROM `%s` WHERE %s LIMIT 1",
            $tableName,
            static::buildWhereForPrimaryKeyFields()
        );
        
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->{$primaryKey->getProperty()}));
        
        return $this;
    }
}