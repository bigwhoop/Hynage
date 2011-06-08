<?php
namespace Hynage\Database;
use Hynage;
use Hynage\Reflection;

abstract class Record
{
    /**
     * Find records by an SQL statement
     * 
     * @param string $sql
     * @param array $params
     * @param bool $singleRecord
     * @return array|Hynage\Database\Record|false
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
     * @param bool $load
     * @return \Hynage\Database\Record|false
     */
    public static function findOne($id, $load = false)
    {
        return self::findOneBy(static::getPrimaryKeyField()->getName(), $id);
    }
    
    
    /**
     * Find all records of this model
     * 
     * @return array
     */
    public static function findAll()
    {
        $db = Connection::getCurrent();
        
        $sql = 'SELECT * '
             . 'FROM `%s`';
        
        $sql = sprintf($sql, static::getTableName(), static::getPrimaryKeyField()->getName());
        
        $stmt = $db->query($sql);
        
        return self::_hydrate($stmt);
    }
    
    
    protected static function _hydrate($data, $singleRecord = false)
    {
        if ($data instanceof \PDOStatement) {
            $data = $data->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        $records = array();
        $class = get_called_class();
        
        foreach ((array)$data as $values) {
            if (!is_array($values)) {
                continue;
            }
            
            $obj = new $class;
            $obj->populate($values);
            
            $records[] = $obj;
        }
        
        // Only one record is expected. Or false if none found.
        if ($singleRecord) {
            return count($records) ? $records[0] : false;
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
     * Return the primary key field name
     * 
     * @return \Hynage\Database\Record\Field|false
     */
    public static function getPrimaryKeyField()
    {
        foreach (self::getFieldDefinitions() as $field) {
            if ($field->isPrimary()) {
                return $field;
            }
        }
        
        return false;
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
            
            $fields[] = new Hynage\Database\Record\Field($name, $propertyName, $type, $length, $attributes);
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
    
    
    /**
     * Set the properties given by a key/value array
     * 
     * @param array $values
     * @return \Hynage\Database\Record
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


    public function isPersistent()
    {
        $primaryKey = static::getPrimaryKeyField();
        return !empty($this->{$primaryKey->getProperty()});
    }
    
    
    public function save()
    {
        $db = Connection::getCurrent();
        
        $primaryKey = static::getPrimaryKeyField();
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
        }
        
        // Update
        else {
            $placeholders = array();
            foreach (array_keys($values) as $field) {
                $placeholders[] = "`$field` = ?";
            }
            
            $sql = sprintf(
                "UPDATE `%s` SET %s WHERE `{$primaryKey->getName()}` = ?",
                $tableName,
                join(', ', $placeholders)
            );
            
            $values[$primaryKey->getName()] = $this->{$primaryKey->getProperty()};
            
            $stmt = $db->prepare($sql);
            $stmt->execute(array_values($values));
        }
        
        return $this;
    }
    
    
    public function delete()
    {
        $db = Connection::getCurrent();
        
        $primaryKey = static::getPrimaryKeyField();
        $tableName  = static::getTableName();
        
        $sql = sprintf(
            "DELETE FROM `%s` WHERE `%s` = ? LIMIT 1",
            $tableName,
            $primaryKey->getName()
        );
        
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->{$primaryKey->getProperty()}));
        
        return $this;
    }
}