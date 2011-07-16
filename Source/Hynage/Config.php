<?php
namespace Hynage;

class Config implements \Iterator
{
    /**
     * @var array
     */
    protected $_data = array();
    
    
    /**
     * Constructor...
     * 
     * @param array $data
     */
    public function __construct(array $data = array())
    {
    	$this->_data = $data;
    }
    
    
    /**
     * Getter to return a specific value.
     * 
     * @param string $key
     * @return mixed|null|Hynage\Config
     */
    public function __get($key)
    {
        return $this->get($key, null, false);
    }
    
    
    /**
     * Return a specific value. If a separator is provided, the key is splitted
     * and recursivly applied to the data.
     * 
     * @param string $key
     * @param mixed $defaultValue
     * @param string|false $separator
     */
    public function get($key, $defaultValue = null, $separator = '.')
    {
        $data  = $this->_data;
        $keys  = false === $separator
               ? array((string)$key)
               : explode($separator, $key);
        
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                if (is_array($data[$key])) {
                    $data = $data[$key];
                } else {
                    return $data[$key];
                }
            } else {
                return $defaultValue;
            }
        }
        
        return new self($data);
    }


    public function set($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }


    public function has($key)
    {
        return array_key_exists($key, $this->_data);
    }

    
    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->_data[$key]);
        }

        return $this;
    }
    
    
    public function getData()
    {
        return $this->_data;
    }
    
    
    public function toArray()
    {
        return (array)$this->getData();
    }
    
    
    public function current()
    {
        return current($this->_data);
    }
    
    public function next()
    {
        return next($this->_data);
    }
    
    public function key()
    {
        return key($this->_data);
    }
    
    public function valid()
    {
        return null !== $this->key();
    }
    
    public function rewind()
    {
        reset($this->_data);
    }
}