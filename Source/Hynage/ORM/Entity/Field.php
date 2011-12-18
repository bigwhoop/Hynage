<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM\Entity;
use Hynage;

class Field
{
    /**
     * @var string
     */
    protected $_name;
    
    /**
     * @var string
     */
    protected $_property;
    
    /**
     * @var string
     */
    protected $_type;
    
    /**
     * @var int|null
     */
    protected $_length;
    
    /**
     * @var array
     */
    protected $_attributes = array();
    

    /**
     * @param string $name
     * @param string $property
     * @param string $type
     * @param int $length
     * @param array $attributes
     */
    public function __construct($name, $property, $type, $length, array $attributes)
    {
        $this->_name       = $name;
        $this->_property   = $property;
        $this->_type       = $type;
        $this->_length     = $length;
        $this->_attributes = $attributes;
    }
    
    
    public function getName()
    {
        return $this->_name;
    }
    
    
    public function getProperty()
    {
        return $this->_property;
    }
    
    
    public function getType()
    {
        return $this->_type;
    }
    
    
    /**
     * Return whether this field is a primary key
     * 
     * @return bool
     */
    public function isPrimary()
    {
        return isset($this->_attributes['primary']) && $this->_attributes['primary'];
    }
    
    
    /**
     * Return whether this field is auto incremented
     * 
     * @return bool
     */
    public function isAutoIncrement()
    {
        return isset($this->_attributes['autoIncrement']) && $this->_attributes['autoIncrement'];
    }
    
    
    /**
     * Return whether this field has the NOT NULL flag
     * 
     * @return bool
     */
    public function isNotNull()
    {
        return isset($this->_attributes['notNull']) && $this->_attributes['notNull'];
    }


    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return isset($this->_attributes['default']) ? $this->_attributes['default'] : null;
    }
}