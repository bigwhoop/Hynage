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
use Hynage,
    Hynage\Reflection\ReflectionClass,
    Hynage\ORM\Entity\Field,
    Hynage\ORM\Entity\Proxy;

abstract class Entity implements ExportStrategy\Exportable
{
    /**
     * @bool
     */
    protected $isPersistent = false;

    /**
     * @var array
     */
    private $proxies = array();


    /**
     * @return string
     * @throws Entity\InvalidDefinitionException
     */
    static public function getClassNameOfEntityDefinition()
    {
        $class = new \ReflectionClass(get_called_class());

        while ($class instanceof \ReflectionClass)
        {
            if (false !== strpos($class->getDocComment(), 'HynageEntityType')) {
                return $class->getName();
            }

            $class = $class->getParentClass();
        }

        throw new Entity\InvalidDefinitionException('Could not find class with table/field definition.');
    }
    
    
    /**
     * Generate a CREATE TABLE statement
     * 
     * @return string
     */
    public static function generateCreateTableStatement()
    {
        $tableName = self::getEntityType();
        
        // Create table
        $sql = sprintf('CREATE TABLE `%s` (' . PHP_EOL, $tableName);
        
        $primaryKeys = array();
        
        // Add columns
        $reflectionClass = new Reflection\ReflectionClass(self::getClassNameOfEntityDefinition());
        foreach ($reflectionClass->getProperties() as $property) {
            $definition = $property->getAnnotation('HynageColumn');
            if (!is_array($definition)) {
                continue;
            }
            
            $name = isset($definition['name'])
                  ? $definition['name']
                  : ltrim($property->name, '_');
            
            $default = $property->getAnnotation('HynageColumnDefault');

            if (isset($definition['type'])) {
                if ($definition['type'] == 'enum') {
                    if (!isset($definition['values'])) {
                        throw new Entity\InvalidDefinitionException("Missing 'values' property in @HynageColumn annotation for enum data type $name.");
                    }

                    $enumValues = array();
                    foreach (explode(',', $definition['values']) as $enumValue) {
                        $enumValues[] = "'$enumValue'";
                    }

                    $definition['type'] = 'ENUM(' . join(', ', $enumValues) . ')';
                    unset($definition['length']);
                } else {
                    $definition['type'] = strtoupper($definition['type']);
                }
            }

            $sql .= sprintf(
                '    `%s` %s%s%s%s%s%s,' . PHP_EOL,
                $name,
                isset($definition['type']) ? $definition['type'] : 'VARCHAR',
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
     * @param string $name
     * @param Entity\Proxy $proxy
     * @return Entity
     */
    public function setProxy($name, Proxy $proxy)
    {
        $this->proxies[$name] = $proxy;
        return $this;
    }


    /**
     * @param $name
     * @return Entity\Proxy
     * @throws \OutOfBoundsException
     */
    public function getProxy($name)
    {
        if (!isset($this->proxies[$name])) {
            throw new \OutOfBoundsException("No proxy with name '$name' available.");
        }

        return $this->proxies[$name];
    }


    /**
     * @param string $name
     * @return Entity
     */
    public function resolveProxy($name)
    {
        $proxy = $this->getProxy($name);
        $this->$name = $proxy->load();

        return $this;
    }


    /**
     * @param \Hynage\ORM\Entity\Field|string $field
     * @return mixed
     * @throws Entity\InvalidDefinitionException
     */
    public function getValue($field)
    {
        if (!$field instanceof Field) {
            $field = static::getFieldByName($field);
        }

        $property = $field->getProperty();

        $reflectionClass = new \ReflectionClass(get_called_class());
        if (!$reflectionClass->hasProperty($property)) {
            throw new Entity\InvalidDefinitionException('No such field: ' . $field->getName());
        }

        return $this->$property;
    }


    /**
     * @param \Hynage\ORM\Entity\Field|string $field
     * @param mixed $value
     * @return \Hynage\ORM\Entity
     * @throws Entity\InvalidDefinitionException
     */
    public function setValue($field, $value)
    {
        if (!$field instanceof Field) {
            $field = static::getFieldByName($field);
        }

        $property = $field->getProperty();

        $reflectionClass = new \ReflectionClass(get_called_class());
        if (!$reflectionClass->hasProperty($property)) {
            throw new Entity\InvalidDefinitionException('No such field: ' . $field->getName());
        }

        $this->$property = $value;

        return $this;
    }


    /**
     * Export this Entity. Default is array.
     *
     * @param \Hynage\ORM\ExportStrategy\Exporting $strategy
     * @return mixed
     */
    public function export(ExportStrategy\Exporting $strategy = null)
    {
        if (!$strategy) {
            $strategy = new ExportStrategy\ArrayStrategy();
        }

        return $strategy->exportEntity($this);
    }
    
    
    /**
     * Set the properties given by a key/value array
     *
     * @param array $fields
     * @param array $values
     * @return \Hynage\ORM\Entity
     */
    public function populate(array $fields, array $values)
    {
        $keys = array_keys($values);
        
        foreach ($fields as $field) {
            if (!$field instanceof Field) {
                throw new \InvalidArgumentException("First argument must be an array containing only instances of 'Hynage\\ORM\\Entity\\Field'.");
            }

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
        return $this->isPersistent;
    }


    /**
     * @param bool $bool
     * @return \Hynage\ORM\Entity
     */
    public function setIsPersistent($bool)
    {
        $this->isPersistent = (bool)$bool;
        return $this;
    }


    /**
     * Return the entity type defined by the '@HynageEntityType' annotation.
     *
     * @return string
     */
    static public function getEntityType()
    {
        $reflectionClass = new ReflectionClass(static::getClassNameOfEntityDefinition());
        return $reflectionClass->getAnnotation('HynageEntityType');
    }


    /**
     * Return the entity's persister name defined by the '@HynagePersister' annotation.
     *
     * @return string
     */
    static public function getPersisterName()
    {
        $reflectionClass = new ReflectionClass(static::getClassNameOfEntityDefinition());
        return $reflectionClass->getAnnotation('HynagePersister');
    }


    /**
     * @return array
     */
    static public function getPrimaryKeyFields()
    {
        $pks = array();

        foreach (static::getFieldDefinitions() as $field) {
            if ($field->isPrimary()) {
                $pks[] = $field;
            }
        }

        return $pks;
    }


    /**
     * @param string $fieldName
     * @return \Hynage\ORM\Entity\Field|false
     */
    static public function getFieldByName($fieldName)
    {
        foreach (static::getFieldDefinitions() as $field) {
            if ($field->getName() === $fieldName) {
                return $field;
            }
        }

        return false;
    }


    /**
     * @param string $propertyName
     * @return \Hynage\ORM\Entity\Field|false
     */
    static public function getFieldByProperty($propertyName)
    {
        foreach (static::getFieldDefinitions() as $field) {
            if ($field->getProperty() === $propertyName) {
                return $field;
            }
        }

        return false;
    }


    /**
     * @return array
     */
    static public function getFieldDefinitions()
    {
        $fields = array();

        $reflectionClass = new ReflectionClass(static::getClassNameOfEntityDefinition());

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

            $fields[] = new Field($name, $propertyName, $type, $length, $attributes);
        }

        return $fields;
    }
}