<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
     * @return \Hynage\Config|mixed
     */
    public function __get($key)
    {
        return $this->get($key, null, false);
    }


    /**
     * @param string $key
     * @param mixed  $value
     * @return Config
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
        return $this;
    }
    
    
    /**
     * Return a specific value. If a separator is provided, the key is splitted
     * and recursivly applied to the data.
     * 
     * @param string $key
     * @param mixed $defaultValue
     * @param string|false $separator
     * @return \Hynage\Config|mixed
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


    /**
     * @param string $key
     * @param mixed $value
     * @return Config
     */
    public function set($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }


    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_data);
    }


    /**
     * @param string $key
     * @return Config
     */
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